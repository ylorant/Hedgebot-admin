<?php
namespace Hedgebot\Plugin\AnnouncementsBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnnouncementsController extends BaseController
{
    /** @var array Time intervals and their divisors combos */
    const TIME_INTERVALS = [
        ['s', 1], 
        ['m', 60],
        ['h', 3600],
        ['d', 86400]
    ];

    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Announcements", $this->generateUrl("announcements_list"));
    }

    /**
     * @Route("/announcements", name="announcements_list")
     */
    public function announcementsListAction(Request $request)
    {
        $templateVars = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $serverEndpoint = $this->get('hedgebot_api')->endpoint('/server');
        $templateVars['messages'] = (array) $endpoint->getMessages();
        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();
        $templateVars['intervals'] = $this->getFormattedIntervals($templateVars['availableChannels']);

        return $this->render('HedgebotAnnouncementsBundle::route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/announcements/message/delete/{id}", options = { "expose" = true }, name="announcements_message_delete")
     */
    public function deleteMessageAction($id)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $deleted = $endpoint->deleteMessage($id);

        $response = new JsonResponse();
        $response->setData($deleted);

        return $response;
    }

    /**
     * @Route("/announcements/message/save/{id}", options = { "expose" = true }, defaults = { "id" = ""}, name="announcements_message_save")
     */
    public function saveMessageAction($id, Request $request)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $data = $request->request->all();
        $newMessageId = null;

        // If no channel is specified, create an empty array to avoid errors
        if(empty($data['channels'])) {
            $data['channels'] = [];
        }

        if(empty($id)) {
            $newMessageId = $endpoint->addMessage($data['message'], $data['channels']);
            $saved = true;
        } else {
            $saved = $endpoint->editMessage($id, $data['message'], $data['channels']);
        }

        $response = new JsonResponse();
        $response->setData($newMessageId ? $newMessageId : $saved);

        return $response;
    }

    /**
     * @Route("/announcements/interval/save/{channel}", options = { "expose" = true }, name="announcements_interval_save")
     */
    public function saveIntervalAction($channel, Request $request)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $data = $request->request->all();
        $time = $this->convertHumanReadableToTime($data['time']);
        $saved = false;

        if($time !== false) {
            if(filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN)) {
                $saved = $endpoint->setInterval($channel, $time);
            } else {
                $saved = $endpoint->removeInterval($channel);
            }
        }

        $response = new JsonResponse();
        $response->setData($saved);

        return $response;
    }

    /**
     * Gets the intervals for each available channel in a view-friendly way.
     * 
     * @param array $channels The list of channels to populate.
     * 
     * @return array The list of intervals per channel.
     */
    protected function getFormattedIntervals(array $channels)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $definedIntervals = (array) $endpoint->getIntervals();
        $intervals = [];

        foreach($channels as $channel) {
            $intervals[$channel] = [
                "channel" => $channel,
                "enabled" => false,
                "time" => null
            ];
        }

        foreach($definedIntervals as $definedInterval) {
            if(isset($intervals[$definedInterval->channel])) {
                $intervals[$definedInterval->channel]['time'] = $this->convertIntervalToHumanReadable((int) $definedInterval->time);
                $intervals[$definedInterval->channel]['enabled'] = true;
            }
        }

        return $intervals;
    }

    /**
     * Converts an interval time from seconds to an human readable form (for example 15m17s).
     * 
     * @param int $time The time in seconds to convert.
     */
    protected function convertIntervalToHumanReadable($time)
    {
        $index = -1;
        $humanTime = "";
        
        // Find the biggest interval to divide by
        $t = $time;
        while($t > 1) {
            $t = $time / self::TIME_INTERVALS[++$index][1];
        }

        // Go to the previous interval, to have something between 1 and its smaller division
        $index--;
        
        // Create the string from the time in seconds 
        $intervalSums = 0;
        for($i = $index; $i >= 0; $i--) {
            $timeInterval = floor(($time - $intervalSums) / self::TIME_INTERVALS[$i][1]);
            $intervalSums += $timeInterval * self::TIME_INTERVALS[$i][1];

            if($timeInterval > 0) {
                $humanTime .= $timeInterval . self::TIME_INTERVALS[$i][0];
            }
        }

        return $humanTime;
    }

    /**
     * Converts an human-readable time interval to its representation in seconds.
     * 
     * @param string $humanTime The time interval in human readable form.
     * 
     * @return int|bool The time in seconds if successful, false if the input time is malformed.
     */
    protected function convertHumanReadableToTime($humanTime)
    {
        $time = 0;
        $modifiers = join("", array_column(self::TIME_INTERVALS, 0));
        $pattern = "/([0-9]+[" . $modifiers . "])/";

        // Match and get the multipliers on the human time
        $matchesCount = preg_match_all($pattern, $humanTime, $matches);
        if($matchesCount == 0) {
            return false;
        }

        // Go through matches and apply multipliers
        foreach ($matches[1] as $value) {
            $modifierInfo = null;
            $modifier = substr($value, -1);

            foreach(self::TIME_INTERVALS as $m) {
                if($m[0] == $modifier) {
                    $modifierInfo = $m;
                    break;
                }
            }

            $time += intval(substr($value, 0, -1)) * $modifierInfo[1];
        }

        return $time;
    }
}

<?php

namespace App\Modules\Announcements\Controller;

use App\Controller\BaseController;
use App\Helper\DateTimeHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnnouncementsController extends BaseController
{
    /** @var string */
    protected const ENDPOINT_PATH = '/plugin/announcements';

    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        // Bad "breandcrumb x translator" usage, @see https://github.com/mhujer/BreadcrumbsBundle/issues/26
        $this->breadcrumbs->addItem($this->translator->trans('title.announcements', [], 'announcements'), $this->generateUrl("announcements_list"));
    }

    /**
     * @Route("/announcements", name="announcements_list")
     * @param Request $request
     * @return Response
     */
    public function announcementsList(Request $request)
    {
        $templateVars = [];

        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $serverEndpoint = $this->apiClientService->endpoint('/server');
        $templateVars['messages'] = (array) $endpoint->getMessages();
        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();
        $templateVars['intervals'] = $this->getFormattedIntervals($templateVars['availableChannels']);

        return $this->render('announcements/route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/announcements/message/delete/{id}", options = { "expose" = true }, name="announcements_message_delete")
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteMessage($id)
    {
        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $deleted = $endpoint->deleteMessage($id);

        $response = new JsonResponse();
        $response->setData($deleted);

        return $response;
    }

    /**
     * @Route("/announcements/message/save/{id}", options = { "expose" = true }, defaults = { "id" = ""}, name="announcements_message_save")
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function saveMessage($id, Request $request): JsonResponse
    {
        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();
        $newMessageId = null;

        // If no channel is specified, create an empty array to avoid errors
        if (empty($data['channels'])) {
            $data['channels'] = [];
        }

        if (empty($id)) {
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
     *
     * @param string $channel
     * @param Request $request
     * @return JsonResponse
     */
    public function saveInterval(string $channel, Request $request): JsonResponse
    {
        $dateTimeHelper = new DateTimeHelper();
        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();
        $time = $dateTimeHelper->convertHumanReadableToTime($data['time']);
        $messages = (int) $data['messages'];
        $enabled = (bool) $data['enabled'];
        $saved = false;

        if ($time !== false) {
            if (filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN)) {
                $saved = $endpoint->setInterval($channel, $time, $messages, $enabled);
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
    protected function getFormattedIntervals(array $channels): array
    {
        $dateTimeHelper = new DateTimeHelper();
        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $definedIntervals = (array) $endpoint->getIntervals();
        $intervals = [];

        foreach ($channels as $channel) {
            $intervals[$channel] = [
                "channel" => $channel,
                "enabled" => false,
                "messages" => null,
                "time" => null
            ];
        }

        foreach ($definedIntervals as $definedInterval) {
            if (isset($intervals[$definedInterval->channel])) {
                $intervals[$definedInterval->channel]['time'] = $dateTimeHelper->convertIntervalToHumanReadable(
                    (int) $definedInterval->time
                );
                $intervals[$definedInterval->channel]['messages'] = $definedInterval->messages;
                $intervals[$definedInterval->channel]['enabled'] = (bool) $definedInterval->enabled;
            }
        }

        return $intervals;
    }
}

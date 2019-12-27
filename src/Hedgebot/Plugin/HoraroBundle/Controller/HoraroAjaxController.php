<?php
namespace Hedgebot\Plugin\HoraroBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HoraroAjaxController extends BaseController
{
    /**
     * @Route("/horaro/ajax/schedule/{identSlug}", options = { "expose" = true }, name="horaro_ajax_get_schedule")
     */
    public function getScheduleAction($identSlug)
    {
        $markdownParser = $this->get('markdown.parser');
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/horaro');
        $schedule = $endpoint->getSchedule($identSlug);
        $scheduleData = $endpoint->getScheduleData($identSlug);
        
        foreach($scheduleData->items as &$item) {
            foreach($item->data as &$itemData) {
                $itemData = strip_tags($markdownParser->transformMarkdown($itemData));
                dump($itemData);
            }
        }

        $response = new JsonResponse();
        $response->setData([
            'scheduleData' => $scheduleData,
            'schedule' => $schedule 
        ]);

        return $response;
    }

    /**
     * @Route("/horaro/ajax/schedule/{identSlug}/action/{action}", options = { "expose" = true }, name="horaro_ajax_schedule_action")
     */
    public function actionScheduleAction(Request $request, $identSlug, $action)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/horaro');

        // Actions
        switch($action)
        {
            // Previous item
            case 'previous':
                $result = $endpoint->previousItem($identSlug);
                break;
            
            // Pause/resume the schedule
            case 'pause':
                // Get schedule 
                $schedule = $endpoint->getSchedule($identSlug);

                if ($schedule->paused) {
                    $result = $endpoint->resumeSchedule($identSlug);
                } else {
                    $result = $endpoint->pauseSchedule($identSlug);
                }
                break;
            
            // Next item
            case 'next':
                $result = $endpoint->nextItem($identSlug);
                break;
            
            // Go to specific item
            case 'goto':
                $itemIndex = $request->query->get('item');
                
                if(is_numeric($itemIndex)) {
                    $result = $endpoint->goToItem($identSlug, $itemIndex);
                } else {
                    $result = false;
                }
                break;
        }
        
        $response = new JsonResponse();
        $response->setData($result);

        return $response;
    }
}
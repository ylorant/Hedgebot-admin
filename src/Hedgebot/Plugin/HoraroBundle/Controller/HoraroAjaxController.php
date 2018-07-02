<?php
namespace Hedgebot\Plugin\HoraroBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class HoraroAjaxController extends BaseController
{
    /**
     * @Route("/horaro/ajax/schedule/{identSlug}", options = { "expose" = true }, name="horaro_ajax_get_schedule")
     */
    public function getScheduleAction($identSlug)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/horaro');
        $schedule = $endpoint->getSchedule($identSlug);
        
        $response = new JsonResponse();
        $response->setData($schedule);

        return $response;
    }

    /**
     * @Route("/horaro/ajax/schedule/{identSlug}/action/{action}", options = { "expose" = true }, name="horaro_ajax_schedule_action")
     */
    public function actionScheduleAction($identSlug, $action)
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
        }
        
        $response = new JsonResponse();
        $response->setData($result);

        return $response;
    }
}
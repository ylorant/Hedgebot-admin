<?php
namespace Hedgebot\Plugin\TimerBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TimerAjaxController extends BaseController
{
    /**
     * @Route("/timer/ajax/action/{timerId}/{action}", options = { "expose" = true }, name="timer_ajax_action")
     */
    public function actionTimerAction(Request $request, $timerId, $action)
    {
        $success = true;

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/timer');
        $timer = $endpoint->getTimerById($timerId);

        dump($timer);

        if(empty($timer)) {
            $success = false;
        }

        switch($action) {
            case "start":
                $success = $endpoint->startStopTimer($timerId);
                break;
            
            case "pause":
                $success = $endpoint->pauseResumeTimer($timerId);
                break;
            
            case "reset":
                $success = $endpoint->resetTimer($timerId);
                break;
            
            default:
                $success = false;
        }

        return new JsonResponse($success);
    }
}
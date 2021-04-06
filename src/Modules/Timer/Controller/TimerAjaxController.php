<?php
namespace App\Modules\Timer\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TimerAjaxController extends BaseController
{
    /**
     * @Route("/timer/ajax/action/{timerId}/{action}", options = { "expose" = true }, name="timer_ajax_action")
     * @param Request $request
     * @param $timerId
     * @param $action
     * @return JsonResponse
     */
    public function actionTimerAction(Request $request, $timerId, $action)
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/timer');
        $timer = $endpoint->getTimerById($timerId);

        if (empty($timer)) {
            $success = false;
        } else {
            switch ($action) {
                case "start":
                    $success = $endpoint->startStopTimer($timerId);
                    break;

                case "pause":
                    $success = $endpoint->pauseResumeTimer($timerId);
                    break;

                case "reset":
                    $success = $endpoint->resetTimer($timerId);
                    break;

                case 'playerStop':
                    $player = $request->request->get('player');
                    $success = $endpoint->stopPlayerTimer($timerId, $player);
                    break;

                default:
                    $success = false;
            }
        }

        return new JsonResponse($success);
    }
}

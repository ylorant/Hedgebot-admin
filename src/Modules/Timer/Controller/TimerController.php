<?php
namespace App\Modules\Timer\Controller;

use DateTime;
use App\Controller\BaseController;
use App\Modules\Timer\Helper\TimerHelper;
use Symfony\Component\Routing\Annotation\Route;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class TimerController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $this->breadcrumbs->addItem("Timers", $this->generateUrl("timer_list"));
    }

    /**
     * @Route("/timer", name="timer_list")
     */
    public function timerListAction()
    {
        $templateVars = [];

        $endpoint = $this->apiClientService->endpoint('/plugin/timer');
        $timers = $endpoint->getTimers();
        $remoteTimeInfo = $endpoint->getLocalTime(); // Gets the remote time, despite the method name

        // Creating the datetime for the remote time, but we can't directly use the ISO8601 shorthand
        $remoteTime = DateTime::createFromFormat("Y-m-d\TH:i:sO v", join(' ', (array) $remoteTimeInfo));

        foreach ($timers as &$timer) {
            $timer = TimerHelper::prepareTimer($timer, $remoteTime);
        }

        $templateVars['timers'] = $timers;
        $templateVars['remoteTime'] = $remoteTime->format("c");
        $templateVars['remoteMsec'] = $remoteTime->format("v");

        return $this->render('timer/route/index.html.twig', $templateVars);
    }
}

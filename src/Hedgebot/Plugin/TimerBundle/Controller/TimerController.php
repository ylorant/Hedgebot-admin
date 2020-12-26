<?php
namespace Hedgebot\Plugin\TimerBundle\Controller;

use DateTime;
use Hedgebot\CoreBundle\Controller\BaseController;
use Hedgebot\Plugin\TimerBundle\Helper\TimerHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TimerController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Timers", $this->generateUrl("timer_list"));
    }

    /**
     * @Route("/timer", name="timer_list")
     */
    public function timerListAction()
    {
        $templateVars = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/timer');
        $timers = $endpoint->getTimers();
        $remoteTimeInfo = $endpoint->getLocalTime(); // Gets the remote time, despite the method name

        // Creating the datetime for the remote time, but we can't directly use the ISO8601 shorthand
        $remoteTime = DateTime::createFromFormat("Y-m-d\TH:i:sO v", join(' ', (array) $remoteTimeInfo));

        foreach($timers as &$timer) {
            $timer = TimerHelper::prepareTimer($timer, $remoteTime);
        }

        $templateVars['timers'] = $timers;
        $templateVars['remoteTime'] = $remoteTime->format("c");
        $templateVars['remoteMsec'] = $remoteTime->format("v");

        return $this->render('HedgebotTimerBundle::route/index.html.twig', $templateVars);
    }
}
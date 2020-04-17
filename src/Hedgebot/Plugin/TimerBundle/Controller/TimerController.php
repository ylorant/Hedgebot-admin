<?php
namespace Hedgebot\Plugin\TimerBundle\Controller;

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

        foreach($timers as $timer) {
            $timer->formattedTime = TimerHelper::formatTimerTime($timer);
        }

        $templateVars['timers'] = $timers;

        return $this->render('HedgebotTimerBundle::route/index.html.twig', $templateVars);
    }
}
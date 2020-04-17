<?php
namespace Hedgebot\Plugin\TimerBundle\Controller\Front;

use Hedgebot\Plugin\TimerBundle\Helper\TimerHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TimerFrontController extends Controller
{
    /**
     * @Route("/timer/{id}/embed", name="timer_front_embed")
     */
    public function timerEmbedAction($id)
    {
        $templateVars = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/timer');
        $timer = $endpoint->getTimerById($id);

        if(empty($timer)) {
            throw new HttpException(404, "Timer not found.");
        }

        $timer->formattedTime = TimerHelper::formatTimerTime($timer);
        $templateVars['timer'] = $timer;

        return $this->render('HedgebotTimerBundle::route/public/embed.html.twig', $templateVars);
    }
}
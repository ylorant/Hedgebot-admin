<?php
namespace Hedgebot\Plugin\TimerBundle\Controller\Front;

use Hedgebot\Plugin\TimerBundle\Helper\TimerHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TimerFrontController extends Controller
{
    /**
     * @Route("/timer/{id}/embed", name="timer_front_embed")
     */
    public function timerEmbedAction(Request $request, $id)
    {
        $templateVars = [];
        $player = $request->query->get('player') ?? null;

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/timer');
        $timer = $endpoint->getTimerById($id);

        $timer->players = (array) $timer->players;

        if(empty($timer)) {
            throw new HttpException(404, "Timer not found.");
        }

        $timer = TimerHelper::prepareTimer($timer);
        $templateVars['timer'] = $timer;
        $templateVars['player'] = $player;

        return $this->render('HedgebotTimerBundle::route/public/embed.html.twig', $templateVars);
    }
}
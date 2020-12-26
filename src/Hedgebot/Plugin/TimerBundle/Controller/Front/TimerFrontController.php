<?php
namespace Hedgebot\Plugin\TimerBundle\Controller\Front;

use DateTime;
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
        $remoteTimeInfo = $endpoint->getLocalTime(); // Gets the remote time, despite the method name

        // Creating the datetime for the remote time, but we can't directly use the ISO8601 shorthand
        $remoteTime = DateTime::createFromFormat("Y-m-d\TH:i:sO v", join(' ', (array) $remoteTimeInfo));


        $timer->players = (array) $timer->players;

        if(empty($timer)) {
            throw new HttpException(404, "Timer not found.");
        }

        $timer = TimerHelper::prepareTimer($timer, $remoteTime);
        $templateVars['timer'] = $timer;
        $templateVars['player'] = $player;
        $templateVars['remoteTime'] = $remoteTime->format("c");
        $templateVars['remoteMsec'] = $remoteTime->format("v");

        return $this->render('HedgebotTimerBundle::route/public/embed.html.twig', $templateVars);
    }
}
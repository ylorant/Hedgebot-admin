<?php
namespace Hedgebot\Plugin\StreamControlBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class StreamSettingsAjaxController extends BaseController
{
    /**
     * @Route("/streamcontrol/ajax/settings", options = { "expose" = true }, name="streamcontrol_ajax_update_settings")
     */
    public function updateSettingsAction(Request $request)
    {
        $requestData = $request->request->all();
        $returnData = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/streamcontrol');
        $currentInfo = $endpoint->setChannelInfo($requestData['channel'], $requestData['title'], $requestData['game']);
        
        if(!$currentInfo) {
            $returnData['success'] = false;
        } else {
            $returnData['success'] = true;
            $returnData['info'] = [
                'title' => $currentInfo->status,
                'game'  => $currentInfo->game
            ];
        }

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/streamcontrol/ajax/commercials/{channel}", options = { "expose" = true }, name="streamcontrol_ajax_start_commercials")
     */
    public function startCommercialsAction($channel)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/streamcontrol');
        $adsStarted = $endpoint->startAds($channel, 90);

        return new JsonResponse(['success' => $adsStarted]);
    }

    /**
     * @Route("/streamcontrol/ajax/host/{channel}", options = { "expose" = true }, name="streamcontrol_ajax_host_channel")
     */
    public function hostChannelAction($channel, Request $request)
    {
        $requestData = $request->request->all();

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/streamcontrol');
        $endpoint->hostChannel($channel, $requestData['target']);

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/streamcontrol/ajax/raid/{channel}", options = { "expose" = true }, name="streamcontrol_ajax_raid_channel")
     */
    public function raidChannelAction($channel, Request $request)
    {
        $requestData = $request->request->all();

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/streamcontrol');
        $endpoint->raidChannel($channel, $requestData['target']);

        return new JsonResponse(['success' => true]);
    }
}
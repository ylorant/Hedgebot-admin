<?php

namespace App\Modules\StreamControl\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class StreamSettingsAjaxController extends BaseController
{
    /**
     * @Route("/streamcontrol/ajax/settings", options = { "expose" = true }, name="streamcontrol_ajax_update_settings")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $requestData = $request->request->all();
        $returnData = ['success' => false];

        $endpoint = $this->apiClientService->endpoint('/plugin/streamcontrol');
        $currentInfo = $endpoint->setChannelInfo(
            $requestData['channel'],
            $requestData['title'],
            $requestData['game_name']
        );

        if ($currentInfo) {
            $returnData['success'] = true;
            $returnData['info'] = [
                'title' => $currentInfo->title,
                'game_name' => $currentInfo->game_name
            ];
        }

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/streamcontrol/ajax/commercials/{channel}", options = { "expose" = true }, name="streamcontrol_ajax_start_commercials")
     * @param $channel
     * @return JsonResponse
     */
    public function startCommercials($channel): JsonResponse
    {
        $returnData = ['success' => false];

        $endpoint = $this->apiClientService->endpoint('/plugin/streamcontrol');
        $returnData['success'] = $endpoint->startAds($channel, 90);

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/streamcontrol/ajax/host/{channel}", options = { "expose" = true }, name="streamcontrol_ajax_host_channel")
     */
    public function hostChannel($channel, Request $request): JsonResponse
    {
        $requestData = $request->request->all();
        $returnData = ['success' => true];

        $endpoint = $this->apiClientService->endpoint('/plugin/streamcontrol');
        $endpoint->hostChannel($channel, $requestData['target']);

        // Return always true here since we cannot check that the host command executed correctly
        return new JsonResponse($returnData);
    }

    /**
     * @Route("/streamcontrol/ajax/raid/{channel}", options = { "expose" = true }, name="streamcontrol_ajax_raid_channel")
     * @param $channel
     * @param Request $request
     * @return JsonResponse
     */
    public function raidChannel($channel, Request $request): JsonResponse
    {
        $requestData = $request->request->all();
        $returnData = ['success' => true];

        $endpoint = $this->apiClientService->endpoint('/plugin/streamcontrol');
        $endpoint->raidChannel($channel, $requestData['target']);

        // Return always true here since we cannot check that the raid command executed correctly
        return new JsonResponse($returnData);
    }
}

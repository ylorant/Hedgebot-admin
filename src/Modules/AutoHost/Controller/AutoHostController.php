<?php

namespace App\Modules\AutoHost\Controller;

use App\Controller\BaseController;
use App\Helper\DateTimeHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AutoHostController extends BaseController
{
    /** @var string */
    protected const ENDPOINT_PATH = '/plugin/autohost';

    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        // Bad "breandcrumb x translator" usage, @see https://github.com/mhujer/BreadcrumbsBundle/issues/26
        $this->breadcrumbs->addItem($this->translator->trans('title.autohost', [], 'autohost'), $this->generateUrl("autohost_list"));
    }

    /**
     * @Route("/autohost", options = { "expose" = true }, name="autohost_list")
     * @param Request $request
     * @return Response
     */
    public function autohostList(Request $request)
    {
        $templateVars = [];
        $dateTimeHelper = new DateTimeHelper();

        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $serverEndpoint = $this->apiClientService->endpoint('/server');
        $data = $request->request->all();

        $templateVars['hosts'] = (array) $endpoint->getHosts();
        foreach ($templateVars['hosts'] as $host) {
            $templateVars['hosts'][$host->channel]->time = $dateTimeHelper->convertIntervalToHumanReadable((int) $host->time);
        }

        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();

        // Selected channel is set from the request data if a channel has been selected by the user,
        // else we default to the first available channel from the bot
        $templateVars['selectedChannel'] = !empty($templateVars['availableChannels']) ? $templateVars['availableChannels'][0] : null;
        if (key_exists('selectedHost', $data) && isset($data['selectedHost']) && in_array($data['selectedHost'], $templateVars['availableChannels'])) {
            $templateVars['selectedChannel'] = $data['selectedHost'];
        }

        // If the host configuration hasn't been defined for the channel yet, initialize
        // it as an empty object
        // TODO: Maybe find a way to pull the empty host config object from the bot ?
        if (!isset($templateVars['hosts'][$templateVars['selectedChannel']])) {
            $templateVars['hosts'][$templateVars['selectedChannel']] = (object) [
                'channel' => $templateVars['selectedChannel'],
                'enabled' => false,
                'time' => null,
                'hostedChannels' => []
            ];
        }

        return $this->render('autohost/route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/autohost/configuration/save", options = { "expose" = true }, defaults = {}, name="autohost_configuration_save")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveConfigurationAction(Request $request)
    {
        $dateTimeHelper = new DateTimeHelper();
        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();
        $enabled = $data['enabled'] == 'true';
        $timeInterval = $dateTimeHelper->convertHumanReadableToTime($data['timeInterval']);
        $whiteList = explode(',', $data['whiteList']);
        $blackList = explode(',', $data['blackList']);

        $saved = $endpoint->editHostConfiguration($data['channel'], $enabled, $timeInterval, $whiteList, $blackList);

        $response = new JsonResponse();
        $response->setData($saved);

        return $response;
    }

    /**
     * @Route("/autohost/hosted/save", options = { "expose" = true }, defaults = {}, name="autohost_hosted_save")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveHostedAction(Request $request)
    {
        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();
        $newHostedId = null;
        $enabled = $data['enabled'] == 'true';

        if (empty($data['id'])) {
            $newHostedId = $endpoint->addHostedChannel($data['channel'], $data['hosted'], $data['priority'], $enabled);
            $saved = true;
        } else {
            $saved = $endpoint->editHostedChannel($data['channel'], $data['hosted'], $data['priority'], $enabled);
        }

        $response = new JsonResponse();
        $response->setData($newHostedId ? $newHostedId : $saved);

        return $response;
    }

    /**
     * @Route("/autohost/hosted/delete", options = { "expose" = true }, defaults = {}, name="autohost_hosted_delete")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteHostedAction(Request $request)
    {
        $endpoint = $this->apiClientService->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();

        if (empty($data['id'])) {
            $deleted = false;
        } else {
            $deleted = $endpoint->removeHostedChannel($data['channel'], $data['hosted']);
        }

        $response = new JsonResponse();
        $response->setData($deleted);

        return $response;
    }
}

<?php
namespace Hedgebot\Plugin\AutoHostBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Hedgebot\CoreBundle\Helper\DateTimeHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AutoHostController extends BaseController
{
    /** @var string */
    const ENDPOINT_PATH = '/plugin/autohost';

    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("autohost", $this->generateUrl("autohost_list"));
    }

    /**
     * @Route("/autohost", options = { "expose" = true }, name="autohost_list")
     */
    public function autohostListAction(Request $request)
    {
        $templateVars = [];
        $dateTimeHelper = new DateTimeHelper();

        $endpoint = $this->get('hedgebot_api')->endpoint($this::ENDPOINT_PATH);
        $serverEndpoint = $this->get('hedgebot_api')->endpoint('/server');
        $data = $request->request->all();

        $templateVars['hosts'] = (array) $endpoint->getHosts();
        foreach($templateVars['hosts']  as $host) {
            $templateVars['hosts'][$host->channel]->time = $dateTimeHelper->convertIntervalToHumanReadable((int) $host->time);
        }

        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();

        // Get selected channel depends of one parameter in $data when you have already loaded AutoHost page and
        //     want another channel to configure than the first one in channels list
        $templateVars['channelSelected'] = $templateVars['availableChannels'][0];
        if (key_exists('selectedHost', $data) && isset($data['selectedHost'])) {
            $templateVars['channelSelected'] = $data['selectedHost'];
        }

        return $this->render('HedgebotAutoHostBundle::route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/autohost/configuration/save/", options = { "expose" = true }, defaults = {}, name="autohost_configuration_save")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveConfigurationAction(Request $request)
    {
        $dateTimeHelper = new DateTimeHelper();
        $endpoint = $this->get('hedgebot_api')->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();
        $enabled = (bool) $data['enabled'];
        $timeInterval= $dateTimeHelper->convertHumanReadableToTime($data['timeInterval']);
        $whiteList = explode(',', $data['whiteList']);
        $blackList = explode(',', $data['blackList']);

        $saved = $endpoint->editHostConfiguration($data['channel'], $enabled, $timeInterval, $whiteList, $blackList);

        $response = new JsonResponse();
        $response->setData($saved);

        return $response;
    }

    /**
     * @Route("/autohost/hosted/save/", options = { "expose" = true }, defaults = {}, name="autohost_hosted_save")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveHostedAction(Request $request)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();
        $newHostedId = null;
        $enabled = (bool) $data['enabled'];

        if(empty($data['id'])) {
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
     * @Route("/autohost/hosted/delete/", options = { "expose" = true }, defaults = {}, name="autohost_hosted_delete")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteHostedAction(Request $request)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint($this::ENDPOINT_PATH);
        $data = $request->request->all();

        if(empty($data['id'])) {
            $deleted = false;
        } else {
            $deleted = $endpoint->removeHostedChannel($data['channel'], $data['hosted']);
        }

        $response = new JsonResponse();
        $response->setData($deleted);

        return $response;
    }
}
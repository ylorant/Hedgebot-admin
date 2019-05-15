<?php
namespace Hedgebot\Plugin\AutoHostBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AutoHostController extends BaseController
{
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
     * @Route("/autohost", name="autohost_list")
     */
    public function autohostListAction(Request $request)
    {
        $templateVars = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/autohost');
        $serverEndpoint = $this->get('hedgebot_api')->endpoint('/server');
        $data = $request->request->all();

        $templateVars['hosts'] = (array) $endpoint->getHosts();
        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();
        $templateVars['channelSelected'] = $templateVars['availableChannels'][0];
        if (key_exists('selectedHost', $data) && isset($data['selectedHost'])) {
            $templateVars['channelSelected'] = $data['selectedHost'];
        }

        return $this->render('HedgebotAutoHostBundle::route/index.html.twig', $templateVars);
    }
}
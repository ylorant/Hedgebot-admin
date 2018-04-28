<?php
namespace Hedgebot\Plugin\CustomCommandsBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomCommandsController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Custom commands", $this->generateUrl("custom_commands_list"));
    }

	/**
     * @Route("/custom-commands", name="custom_commands_list")
     */
    public function commandListAction(Request $request)
    {
        $templateVars = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/custom-commands');
        $serverEndpoint = $this->get('hedgebot_api')->endpoint('/server');
        $templateVars['commands'] = $endpoint->getCommands();
        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();

        return $this->render('HedgebotCustomCommandsBundle::route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/custom-commands/delete/{name}", options = { "expose"=true }, name="custom_commands_delete")
     */
    public function deleteCommandAction($name)
    {
        return null;
    }

    /**
     * @Route("/custom-commands/save/{name}", options = { "expose"=true }, name="custom_commands_save")
     */
    public function saveCommandAction($name, Request $request)
    {
        $success = false;

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/custom-commands');
        $data = $request->request->all();

        $updated = $endpoint->saveCommand($name, $data);

        $response = new JsonResponse();
        $response->setData($updated);

        return $response;
    }
}
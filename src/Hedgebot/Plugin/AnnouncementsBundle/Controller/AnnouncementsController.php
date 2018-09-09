<?php
namespace Hedgebot\Plugin\AnnouncementsBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnnouncementsController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Announcements", $this->generateUrl("announcements_list"));
    }

    /**
     * @Route("/announcements", name="announcements_list")
     */
    public function announcementsListAction(Request $request)
    {
        $templateVars = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $serverEndpoint = $this->get('hedgebot_api')->endpoint('/server');
        $templateVars['messages'] = (array) $endpoint->getMessages();
        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();

        return $this->render('HedgebotAnnouncementsBundle::route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/announcements/message/delete/{name}", options = { "expose" = true }, name="announcements_message_delete")
     */
    public function deleteMessageAction($name)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $deleted = $endpoint->deleteMessage($name);

        $response = new JsonResponse();
        $response->setData($deleted);

        return $response;
    }

    /**
     * @Route("/announcements/message/save/{name}", options = { "expose" = true }, name="announcements_message_save")
     */
    public function saveMessageAction($name, Request $request)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $data = $request->request->all();

        $updated = $endpoint->saveMessage($name, $data);

        $response = new JsonResponse();
        $response->setData($updated);

        return $response;
    }
}

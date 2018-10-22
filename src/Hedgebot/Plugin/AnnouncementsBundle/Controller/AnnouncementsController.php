<?php
namespace Hedgebot\Plugin\AnnouncementsBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/announcements/message/delete/{id}", options = { "expose" = true }, name="announcements_message_delete")
     */
    public function deleteMessageAction($id)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $deleted = $endpoint->deleteMessage($id);

        $response = new JsonResponse();
        $response->setData($deleted);

        return $response;
    }

    /**
     * @Route("/announcements/message/save/{id}", options = { "expose" = true }, defaults = { "id" = ""}, name="announcements_message_save")
     */
    public function saveMessageAction($id, Request $request)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/announcements');
        $data = $request->request->all();
        $newMessageId = null;

        // If no channel is specified, create an empty array to avoid errors
        if(empty($data['channels'])) {
            $data['channels'] = [];
        }

        if(empty($id)) {
            $newMessageId = $endpoint->addMessage($data['message'], $data['channels']);
            $saved = true;
        } else {
            $saved = $endpoint->editMessage($id, $data['message'], $data['channels']);
        }

        $response = new JsonResponse();
        $response->setData($newMessageId ? $newMessageId : $saved);

        return $response;
    }
}

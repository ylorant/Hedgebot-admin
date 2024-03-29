<?php

namespace App\Modules\CustomCommands\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;
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

        // Bad "breandcrumb x translator" usage, @see https://github.com/mhujer/BreadcrumbsBundle/issues/26
        $this->breadcrumbs->addItem($this->translator->trans('title.customcommands', [], 'customcommands'), $this->generateUrl("custom_commands_list"));
    }

    /**
     * @Route("/custom-commands", name="custom_commands_list")
     */
    public function commandList()
    {
        $templateVars = [];

        $endpoint = $this->apiClientService->endpoint('/plugin/custom-commands');
        $serverEndpoint = $this->apiClientService->endpoint('/server');
        $templateVars['commands'] = $endpoint->getCommands();
        $templateVars['availableChannels'] = $serverEndpoint->getAvailableChannels();

        return $this->render('customcommands/route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/custom-commands/delete/{name}", options = { "expose" = true }, name="custom_commands_delete")
     * @param $name
     * @return JsonResponse
     */
    public function deleteCommand($name)
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/custom-commands');
        $deleted = $endpoint->deleteCommand($name);

        $response = new JsonResponse();
        $response->setData($deleted);

        return $response;
    }

    /**
     * @Route("/custom-commands/save/{name}", options = { "expose" = true }, name="custom_commands_save")
     * @param $name
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCommand($name, Request $request)
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/custom-commands');
        $data = $request->request->all();

        $updated = $endpoint->saveCommand($name, $data);

        $response = new JsonResponse();
        $response->setData($updated);

        return $response;
    }
}

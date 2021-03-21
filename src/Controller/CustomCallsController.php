<?php

namespace App\Controller;

use App\Entity\CustomCall;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CustomCallType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Curl\Curl;
use Symfony\Component\HttpFoundation\Response;

class CustomCallsController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();
        $this->breadcrumbs->addItem("Custom calls", $this->get("router")->generate("custom_calls_index"));
    }

    /**
     * Index page for custom calls, lists the available calls.
     *
     * @Route("/custom-calls", name="custom_calls_index")
     */
    public function indexAction(): Response
    {
        $repo = $this->get('doctrine')->getRepository(CustomCall::class);
        $callList = $repo->findAll();
        return $this->render('core/route/custom-calls/index.html.twig', [
            "calls" => $callList
        ]);
    }

    /**
     * New call/Edit call page.
     *
     * @Route("/custom-calls/new", name="custom_calls_new")
     * @Route("/custom-calls/edit/{id}", name="custom_calls_edit")
     * @param Request $request
     * @param null $id
     * @return RedirectResponse|Response
     */
    public function editCallAction(Request $request, $id = null)
    {
        // Add breadcrumb
        $router = $this->get("router");
        if (!empty($id)) {
            $this->breadcrumbs->addItem("Edit call", $router->generate("custom_calls_edit", ['id' => $id]));
        } else {
            $this->breadcrumbs->addItem("New call", $router->generate("custom_calls_new"));
        }

        $repo = $this->get('doctrine')->getRepository(CustomCall::class);
        $call = new CustomCall();

        // Get the edited entity if ID is present
        if (!empty($id)) {
            $call = $repo->find($id);
            if (empty($call)) {
                $this->addFlash('danger', 'Call not found.');
                return $this->redirect($this->generateUrl('custom_calls_index'));
            }
        }

        $form = $this->createForm(CustomCallType::class, $call);

        // Handle the form
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($call);
            $em->flush();
            $this->addFlash('success', 'The call was successfully saved.');

            // Redirect the user to the call page
            if (empty($id)) {
                return $this->redirect($this->generateUrl('custom_calls_edit', ["id" => $call->getId()]));
            }
        }

        // Fill template vars
        $templateVars = [
            'call' => $call,
            'form' => $form->createView()
        ];
        return $this->render('core/route/custom-calls/call.html.twig', $templateVars);
    }

    /**
     * Deletes a call.
     *
     * @Route("/custom-calls/delete/{id}", name="custom_calls_delete")
     * @param $id
     * @return RedirectResponse
     */
    public function deleteCallAction($id): RedirectResponse
    {
        $repository = $this->getDoctrine()->getRepository(CustomCall::class);
        $call = $repository->find($id);
        if (empty($call)) {
            $this->addFlash('danger', 'Call not found.');
            $this->redirect($this->generateUrl('custom_calls_index'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($call);
        $em->flush();
        $this->addFlash('success', 'Call deleted.');
        return $this->redirect($this->generateUrl('custom_calls_index'));
    }

    /**
     * Executes a call.
     *
     * @Route("/custom-calls/execute/{id}", name="custom_calls_execute")
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function executeCallAction(Request $request, $id): RedirectResponse
    {
        /** @var CustomCall $call */
        $repository = $this->getDoctrine()->getRepository(CustomCall::class);
        $call = $repository->find($id);
        if (empty($call)) {
            throw $this->createNotFoundException("Call not found.");
        }

        $query = new Curl();
        switch ($call->getMethod()) {
            case CustomCall::METHOD_GET:
                $query->get($call->getUrl(), $call->getParameters());
                break;
            case CustomCall::METHOD_POST:
                $query->post($call->getUrl(), $call->getParameters());
                break;
            case CustomCall::METHOD_PUT:
                $query->put($call->getUrl(), $call->getParameters());
                break;
            case CustomCall::METHOD_DELETE:
                $query->delete($call->getUrl(), $call->getParameters());
                break;
        }

        // Check that the call succeeded
        if (!empty($query->response) && $query->httpStatusCode == 200) {
            $this->addFlash("success", "Call was executed successfully.");
        } else {
            $this->addFlash("danger", "Call failed.");
        }

        return $this->redirect($request->headers->get('referer'));
    }
}

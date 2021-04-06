<?php

namespace App\Modules\Horaro\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Modules\Horaro\Form\ScheduleType;
use App\Modules\Horaro\Form\ScheduleURLType;

class HoraroController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        // Bad "breandcrumb x translator" usage, @see https://github.com/mhujer/BreadcrumbsBundle/issues/26
        $this->breadcrumbs->addItem($this->translator->trans('title.horaro', [], 'horaro'), $this->generateUrl("horaro_schedule_list"));
    }

    /**
     * @Route("/horaro", name="horaro_schedule_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function scheduleList(Request $request)
    {
        $templateVars = [];

        $endpoint = $this->apiClientService->endpoint('/plugin/horaro');
        $templateVars['schedules'] = (array) $endpoint->getSchedules();

        $newScheduleForm = $this->createForm(ScheduleURLType::class);
        $newScheduleForm->handleRequest($request);

        if ($newScheduleForm->isSubmitted() && $newScheduleForm->isValid()) {
            $newScheduleUrl = $newScheduleForm->get('url')->getData();
            $identSlug = $endpoint->loadScheduleFromURL($newScheduleUrl);

            if ($identSlug) {
                $this->addFlash('success', 'Schedule has been loaded');
                return $this->redirectToRoute('horaro_schedule_edit', ['identSlug' => $identSlug]);
            } else {
                $this->addFlash('danger', 'The schedule cannot be loaded.');
            }
        }

        $templateVars['newScheduleForm'] = $newScheduleForm->createView();

        return $this->render('horaro/route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/horaro/schedule/edit/{identSlug}", name="horaro_schedule_edit")
     * @param Request $request
     * @param $identSlug
     * @return Response
     */
    public function scheduleEdit(Request $request, $identSlug): Response
    {
        $templateVars = [
            'schedule' => null
        ];

        $endpoint = $this->apiClientService->endpoint('/plugin/horaro');
        $serverEndpoint = $this->apiClientService->endpoint('/server');
        $channels = $serverEndpoint->getAvailableChannels();
        $templateVars['schedule'] = $endpoint->getSchedule($identSlug);

        $this->breadcrumbs->addItem("Edit schedule: " . $identSlug);

        $formSchedule = clone $templateVars['schedule'];
        unset($formSchedule->data);

        // Create and handle the form
        $form = $this->createForm(ScheduleType::class, $formSchedule, ['channels' => $channels]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scheduleData = $form->getData();

            $saved = $endpoint->updateSchedule($identSlug, $scheduleData);

            if ($saved) {
                $this->addFlash('success', 'Schedule saved.');
            } else {
                $this->addFlash('danger', 'Could not save schedule.');
            }
        }

        $templateVars['form'] = $form->createView();

        return $this->render('horaro/route/schedule.html.twig', $templateVars);
    }

    /**
     * @Route("/horaro/schedule/delete/{identSlug}", name="horaro_schedule_delete")
     * @param $identSlug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function scheduleDelete($identSlug)
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/horaro');
        $deleted = $endpoint->deleteSchedule($identSlug);

        if ($deleted) {
            $this->addFlash('success', 'Successfully deleted schedule.');
        } else {
            $this->addFlash('danger', 'Could not delete schedule.');
        }

        return $this->redirectToRoute('horaro_schedule_list');
    }
}

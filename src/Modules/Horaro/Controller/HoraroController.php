<?php
namespace App\Modules\Horaro\Controller;

use App\Controller\BaseController;
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

        $this->breadcrumbs->addItem("Horaro", $this->generateUrl("horaro_schedule_list"));
    }

    /**
     * @Route("/horaro", name="horaro_schedule_list")
     */
    public function scheduleListAction(Request $request)
    {
        $templateVars = [];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/horaro');
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

        return $this->render('Horaro::route/index.html.twig', $templateVars);
    }

    /**
     * @Route("/horaro/schedule/edit/{identSlug}", name="horaro_schedule_edit")
     */
    public function scheduleEditAction(Request $request, $identSlug)
    {
        $templateVars = [
            'schedule' => null
        ];

        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/horaro');
        $serverEndpoint = $this->get('hedgebot_api')->endpoint('/server');
        $channels = $serverEndpoint->getAvailableChannels();
        $templateVars['schedule'] = $endpoint->getSchedule($identSlug);

        $this->breadcrumbs->addItem("Edit schedule: ". $identSlug);

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

        return $this->render('Horaro::route/schedule.html.twig', $templateVars);
    }

    /**
     *@Route("/horaro/schedule/delete/{identSlug}", name="horaro_schedule_delete")
     */
    public function scheduleDeleteAction($identSlug)
    {
        $endpoint = $this->get('hedgebot_api')->endpoint('/plugin/horaro');
        $deleted = $endpoint->deleteSchedule($identSlug);

        if ($deleted) {
            $this->addFlash('success', 'Successfully deleted schedule.');
        } else {
            $this->addFlash('danger', 'Could not delete schedule.');
        }

        return $this->redirectToRoute('horaro_schedule_list');
    }
}

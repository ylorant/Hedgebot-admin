<?php
namespace Hedgebot\CoreBundle\Controller;

use Hedgebot\CoreBundle\Entity\User;
use Hedgebot\CoreBundle\Entity\DashboardLayout;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class SettingsController extends BaseController
{
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Settings");
    }

    /** Widget settings page.
     * @Route("/settings/widgets", name="settings_widgets")
     */
    public function widgetSettingsAction()
    {
        $widgetsContainer = $this->get('dashboard_widgets');
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $router = $this->get("router");
        
        $breadcrumbs->addItem("Widgets", $router->generate("settings_widgets"));

        $templateVars = [];
        $templateVars['widgets'] = $widgetsContainer->getAvailableWidgets();
        $templateVars['layouts'] = $widgetsContainer->getLayouts();
        $templateVars['savedLayout'] = null;

        $userSettings = $this->getUser()->getSettings();
        if (!empty($userSettings->dashboardLayout)) {
            $userLayout = new DashboardLayout($widgetsContainer);
            $userLayout->fromArray($userSettings->dashboardLayout);
            $templateVars['savedLayout'] = $userLayout;
        }

        return $this->render('HedgebotCoreBundle::route/settings/widgets-settings.html.twig', $templateVars);
    }

    /** AJAX: Get settings form for a widget.
     *
     * @Route("/settings/widgets/{widgetName}/form", options = { "expose"=true }, name="settings_widget_param_form")
     */
    public function widgetSettingsGetWidgetParamsFormAction($widgetName)
    {
        $widgetsContainer = $this->get('dashboard_widgets');
        $viewParams = [];

        $widget = $widgetsContainer->getWidgetByName($widgetName);
        $formTypeClass = $widget->getSettingsFormType();

        $formOptions = [
            'entity_manager' => $this->getDoctrine()->getManager(),
            'hedgebot_api'     => $this->get('hedgebot_api')
        ];

        if (!empty($formTypeClass)) {
            $viewParams['form'] = $this->createForm($formTypeClass, null, $formOptions)->createView();
        } else {
            $viewParams['form'] = null;
        }

        return $this->render('HedgebotCoreBundle::route/settings/widget-settings-param-form.html.twig', $viewParams);
    }

    /** AJAX: Save widget settings.
     * Here, the data validated from the form on the page is handled manually,
     * without using Symfony Form manager, because the form is mainly generated
     * using Javascript. And anyway, we mainly save the settings as a JSON
     * object anyway.
     *
     * @Route("/settings/widgets/save", options = { "expose"=true }, name="settings_widgets_save")
     * @Method("POST")
     */
    public function widgetSettingsSaveAction(Request $request)
    {
        $success = false;
        $widgetsContainer = $this->get('dashboard_widgets');

        // Checking if there is data set in the request body
        if ($request->request->has('layout') && $request->request->has('widgets')) {
            $em = $this->get('doctrine')->getManager();

            // Getting POST data
            $layoutModelId = $request->request->get('layout');
            $widgets = $request->request->get('widgets');

            // Fill the layout
            $layoutModel = $widgetsContainer->getLayoutById($layoutModelId);
            $userLayout = new DashboardLayout($widgetsContainer);

            if (!empty($layoutModel)) {
                $userLayout->setType($layoutModelId);

                foreach ($widgets as $widget) {
                    // Avoid errors because data being sent as form values, empty elements aren't sent.
                    if (empty($widget['id'])) {
                        $widget['id'] = null;
                    }

                    // ditto
                    if (empty($widget['settings'])) {
                        $widget['settings'] = [];
                    }

                    $userLayout->addWidget($widget['block'], $widget['type'], $widget['id'], $widget['position'], $widget['settings']);
                }

                // Putting the layout data in the user's settings object
                $user = $this->getUser();
                $userSettings = clone $user->getSettings(); // Cloning the object because Doctrine checks for refs to see if there is data to update, so we have to change the ref manually

                $userSettings->dashboardLayout = $userLayout->toArray();
                $user->setSettings($userSettings);

                $em->persist($user);
                $em->flush();

                $success = true;
            }
        }

        $response = new JsonResponse();
        $response->setData($success);

        return $response;
    }
}

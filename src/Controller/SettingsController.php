<?php
namespace App\Controller;

use App\Entity\DashboardLayout;
use App\Service\DashboardWidgetsManagerService;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use TwitchApi\Exceptions\ClientIdRequiredException;

class SettingsController extends BaseController
{
    /**
     * @var DashboardWidgetsManagerService
     */
    private $dashboardWidgetMS;

    /**
     * Constructor
     * @param RouterInterface $router
     * @param KernelInterface $kernel
     * @param FileLocator $fileLocator
     * @param $layoutPath
     * @param $apiBaseUrl
     * @param $apiAccessToken
     * @throws ClientIdRequiredException
     */
    public function __construct(
        RouterInterface $router,
        KernelInterface $kernel,
        FileLocator $fileLocator,
        $layoutPath,
        $apiBaseUrl,
        $apiAccessToken
    ) {
        parent::__construct($router, $apiBaseUrl, $apiAccessToken);
        $this->dashboardWidgetMS = new DashboardWidgetsManagerService(
            $kernel,
            $this->apiClientService,
            $fileLocator,
            $layoutPath
        );
    }

    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $this->breadcrumbs->addItem("Settings");
    }

    /** Widget settings page.
     * @Route("/settings/widgets", name="settings_widgets")
     */
    public function widgetSettingsAction()
    {
        $router = $this->get("router");

        $this->breadcrumbs->addItem("Widgets", $router->generate("settings_widgets"));

        $templateVars = [];
        $templateVars['widgets'] = $this->dashboardWidgetMS->getAvailableWidgets();
        $templateVars['layouts'] = $this->dashboardWidgetMS->getLayouts();
        $templateVars['savedLayout'] = null;

        $userSettings = $this->getUser()->getSettings();
        if (!empty($userSettings->dashboardLayout)) {
            $userLayout = new DashboardLayout($this->dashboardWidgetMS);
            $userLayout->fromArray($userSettings->dashboardLayout);
            $templateVars['savedLayout'] = $userLayout;
        }

        return $this->render('core/route/settings/widgets-settings.html.twig', $templateVars);
    }

    /** AJAX: Get settings form for a widget.
     *
     * @Route("/settings/widgets/{widgetName}/form", options = { "expose"=true }, name="settings_widget_param_form")
     */
    public function widgetSettingsGetWidgetParamsFormAction($widgetName)
    {
        $viewParams = [];

        $widget = $this->dashboardWidgetMS->getWidgetByName($widgetName);
        $formTypeClass = $widget->getSettingsFormType();

        $formOptions = [
            'entity_manager' => $this->getDoctrine()->getManager(),
            'hedgebot_api'   => $this->apiClientService,
            'widget'         => $widget
        ];

        if (!empty($formTypeClass)) {
            $viewParams['form'] = $this->createForm($formTypeClass, null, $formOptions)->createView();
        } else {
            $viewParams['form'] = null;
        }

        return $this->render('core/route/settings/widget-settings-param-form.html.twig', $viewParams);
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

        // Checking if there is data set in the request body
        if ($request->request->has('layout') && $request->request->has('widgets')) {
            $em = $this->get('doctrine')->getManager();

            // Getting POST data
            $layoutModelId = $request->request->get('layout');
            $widgets = $request->request->get('widgets');

            // Fill the layout
            $layoutModel = $this->dashboardWidgetMS->getLayoutById($layoutModelId);
            $userLayout = new DashboardLayout($this->dashboardWidgetMS);

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

                    $userLayout->addWidget(
                        $widget['block'],
                        $widget['type'],
                        $widget['id'],
                        $widget['position'],
                        $widget['settings']
                    );
                }

                // Putting the layout data in the user's settings object
                $user = $this->getUser();
                // Cloning the object because Doctrine checks for refs to see if there is data to update,
                // so we have to change the ref manually
                $userSettings = clone $user->getSettings();

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

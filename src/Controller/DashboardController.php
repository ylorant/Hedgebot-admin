<?php
namespace App\Controller;

use App\Entity\DashboardLayout;
use App\Service\DashboardWidgetsManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Widget\DefaultWidget\DefaultWidget;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TwitchApi\Exceptions\ClientIdRequiredException;

class DashboardController extends BaseController
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
     * @param TranslatorInterface $translator
     * @param $layoutPath
     * @param $apiBaseUrl
     * @param $apiAccessToken
     * @throws ClientIdRequiredException
     */
    public function __construct(
        RouterInterface $router,
        KernelInterface $kernel,
        FileLocator $fileLocator,
        TranslatorInterface $translator,
        $layoutPath,
        $apiBaseUrl,
        $apiAccessToken
    ) {
        parent::__construct($router, $translator, $apiBaseUrl, $apiAccessToken);
        $this->dashboardWidgetMS = new DashboardWidgetsManagerService(
            $kernel,
            $this->apiClientService,
            $fileLocator,
            $layoutPath
        );
    }

    /**
     * @Route("/dashboard", name="dashboard")
     *
     * @return Response
     */
    public function dashboardAction()
    {
        $widgetList = $this->dashboardWidgetMS->getAvailableWidgets();
        $layouts = $this->dashboardWidgetMS->getLayouts();

        $widgetMap = [];
        foreach ($widgetList as $widget) {
            $widgetMap[$widget->getId()] = $widget;
        }

        $userLayoutArray = [];
        $userSettings = $this->getUser()->getSettings();

        if (!empty($userSettings->dashboardLayout)) {
            $userLayoutArray = $userSettings->dashboardLayout;
        }

        // Restore dashboard layout from db
        $userLayout = new DashboardLayout($this->dashboardWidgetMS);
        $layoutLoaded = $userLayout->fromArray($userLayoutArray);

        // If the layout can't be loaded, then we set a default one that warns him to create one to his likings.
        if (!$layoutLoaded) {
            $defaultWidget = new DefaultWidget();
            $userLayout->setType($this->dashboardWidgetMS::DEFAULT_LAYOUT);
            $userLayout->addWidget('main', $defaultWidget->getId());
        }

        $templateVars = [];
        $templateVars['layout'] = $layouts[$userLayout->getType()];
        $templateVars['userLayout'] = $userLayout;
        $templateVars['widgets'] = $widgetMap;

        return $this->render('core/route/dashboard.html.twig', $templateVars);
    }

    /**
     * @Route("/widget/{widgetType}/{widgetId}", name="widget_update")
     */
    public function updateWidget($widgetType, $widgetId)
    {
        // Getting the widget type class from the manager
        $widget = $this->dashboardWidgetMS->getWidgetByName($widgetType);

        // Getting widget settings
        $userLayout = new DashboardLayout($this->dashboardWidgetMS);
        $userLayoutArray = $this->getUser()->getSettings()->dashboardLayout;
        $userLayout->fromArray($userLayoutArray);

        $widgetData = $userLayout->getWidgetById($widgetId);
        $updatedData = null;

        if ($widgetData && $widget) {
            $updatedData = $widget->update($widgetData->settings);
        }

        return new JsonResponse($updatedData);
    }

    /**
     * Gotta go fast ;)
     *
     * @Route("/gotta-go-fast", name="gotta-go-fast")
     */
    public function gottaGoFastAction()
    {
        return $this->render("core/route/gotta-go-fast.html.twig");
    }

    /**
     * Toggles on and off dark mode.
     *
     * @Route("/dark-mode", name="toggle-dark-mode")
     */
    public function toggleDarkMode(Request $request)
    {
        $darkModeCookie = $request->cookies->get('dark-mode');

        $response = $this->redirectToRoute('dashboard');

        if (!empty($darkModeCookie)) {
            $response->headers->setCookie(new Cookie('dark-mode', "false", time()));
        } else {
            $response->headers->setCookie(new Cookie('dark-mode', "true", time() + (86400 * 30)));
        }

        return $response;
    }
}

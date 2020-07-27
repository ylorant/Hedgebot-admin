<?php
namespace Hedgebot\CoreBundle\Controller;

use Hedgebot\CoreBundle\Entity\DashboardLayout;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Hedgebot\CoreBundle\Widget\DefaultWidget\DefaultWidget;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction()
    {
        $widgetsContainer = $this->get('dashboard_widgets');
        $widgetList = $widgetsContainer->getAvailableWidgets();
        $layouts = $widgetsContainer->getLayouts();

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
        $userLayout = new DashboardLayout($widgetsContainer);
        $layoutLoaded = $userLayout->fromArray($userLayoutArray);

        // If the layout can't be loaded, then we set a default one that warns him to create one to his likings.
        if (!$layoutLoaded) {
            $defaultWidget = new DefaultWidget();
            $userLayout->setType($widgetsContainer::DEFAULT_LAYOUT);
            $userLayout->addWidget('main', $defaultWidget->getId());
        }

        $templateVars = [];
        $templateVars['layout'] = $layouts[$userLayout->getType()];
        $templateVars['userLayout'] = $userLayout;
        $templateVars['widgets'] = $widgetMap;

        return $this->render('HedgebotCoreBundle::route/dashboard.html.twig', $templateVars);
    }

    /**
     * @Route("/widget/{widgetType}/{widgetId}", name="widget_update")
     */
    public function updateWidget($widgetType, $widgetId)
    {
        // Getting the widget type class from the manager
        $widgetsContainer = $this->get('dashboard_widgets');
        $widget = $widgetsContainer->getWidgetByName($widgetType);

        // Getting widget settings
        $userLayout = new DashboardLayout($widgetsContainer);
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
        return $this->render("HedgebotCoreBundle::route/gotta-go-fast.html.twig");
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

        if(!empty($darkModeCookie)) {
            $response->headers->setCookie(new Cookie('dark-mode', "false", time()));
        } else {
            $response->headers->setCookie(new Cookie('dark-mode', "true", time() + (86400 * 30)));
        }

        return $response;
    }
}

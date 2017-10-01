<?php
namespace Hedgebot\CoreBundle\Controller;

use Hedgebot\CoreBundle\Entity\DashboardLayout;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        foreach($widgetList as $widget)
            $widgetMap[$widget->getId()] = $widget;

        // Restore dashboard layout from db
        $userLayout = new DashboardLayout($widgetsContainer);
        $userLayoutArray = $this->getUser()->getSettings()->dashboardLayout;
        $userLayout->fromArray($userLayoutArray);

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
        
        if($widgetData && $widget)
            $updatedData = $widget->update($widgetData->settings);

        return new JsonResponse($updatedData);
    }
}

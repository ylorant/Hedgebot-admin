<?php
namespace Hedgebot\CoreBundle\Interfaces;

interface DashboardWidgetsProviderInterface
{
    /**
     * Returns the dashboard widgets that are available to use on this provider.
     */
    public function getDashboardWidgets();
}

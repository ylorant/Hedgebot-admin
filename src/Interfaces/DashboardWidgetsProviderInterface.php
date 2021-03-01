<?php
namespace App\Interfaces;

use App\Service\ApiClientService;

interface DashboardWidgetsProviderInterface
{
    /**
     * Returns the dashboard widgets that are available to use on this provider.
     * @param ApiClientService $apiClientService
     */
    public function getDashboardWidgets(ApiClientService $apiClientService);
}

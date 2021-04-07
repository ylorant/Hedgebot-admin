<?php

namespace App\Modules\StreamControl;

use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\ModuleInterface;
use App\Service\ApiClientService;
use App\Modules\StreamControl\Widget\StreamSettingsWidget;

class StreamControl implements ModuleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see ModuleInterface::getModuleName()
     */
    public static function getModuleName()
    {
        return 'StreamControl';
    }

    /**
     * @see MenuProviderInterface::getMenu()
     */
    public function getMenu()
    {
        return null;
    }

    /**
     * @param ApiClientService $apiClientService
     * @return StreamSettingsWidget[]
     * @see DashboardWidgetsProviderInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [
            new StreamSettingsWidget($apiClientService)
        ];
    }
}

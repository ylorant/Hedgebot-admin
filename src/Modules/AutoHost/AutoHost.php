<?php
namespace App\Modules\AutoHost;

use App\Service\ApiClientService;
use App\Interfaces\ModuleInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class AutoHost implements ModuleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{

    /**
     * @see ModuleInterface::getModuleName()
     */
    public static function getModuleName()
    {
        return 'AutoHost';
    }

    /**
     * @see MenuProviderInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('title.autohost', 'autohost_list', 'live_tv');
    }

    /**
     * @param ApiClientService $apiClientService
     * @return array
     * @see DashboardWidgetsProviderInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [];
    }
}

<?php
namespace App\Modules\Announcements;

use App\Service\ApiClientService;
use App\Interfaces\ModuleInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class Announcements implements ModuleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{

    /**
     * @see ModuleInterface::getModuleName()
     */
    public static function getModuleName()
    {
        return 'Announcements';
    }

    /**
     * @see MenuProviderInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('title.announcements', 'announcements_list', 'message');
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

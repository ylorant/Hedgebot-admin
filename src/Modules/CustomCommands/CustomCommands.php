<?php
namespace App\Modules\CustomCommands;

use App\Service\ApiClientService;
use App\Interfaces\ModuleInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class CustomCommands implements ModuleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{

    /**
     * @see ModuleInterface::getModuleName()
     */
    public static function getModuleName()
    {
        return 'CustomCommands';
    }

    /**
     * @see MenuProviderInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('title.customcommands', 'custom_commands_list', 'list');
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

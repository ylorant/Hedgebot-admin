<?php
namespace App\Modules\HedgebotCustomCommandsBundle;

use App\Service\ApiClientService;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use App\Interfaces\PluginInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class HedgebotCustomCommandsBundle extends Bundle implements PluginInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{

    /**
     * @see PluginInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'CustomCommands';
    }

    /**
     * @see PluginInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('Custom commands', 'custom_commands_list', 'list');
    }

    /**
     * @param ApiClientService $apiClientService
     * @return array
     * @see PluginInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [];
    }
}

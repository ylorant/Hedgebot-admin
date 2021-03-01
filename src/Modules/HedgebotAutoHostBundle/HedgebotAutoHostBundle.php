<?php
namespace App\Modules\HedgebotAutoHostBundle;

use App\Service\ApiClientService;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use App\Interfaces\PluginInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class HedgebotAutoHostBundle extends Bundle implements PluginInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{

    /**
     * @see PluginInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'AutoHost';
    }

    /**
     * @see PluginInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('AutoHost', 'autohost_list', 'live_tv');
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

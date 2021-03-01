<?php
namespace App\Modules\HedgebotAnnouncementsBundle;

use App\Service\ApiClientService;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use App\Interfaces\PluginInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class HedgebotAnnouncementsBundle extends Bundle implements PluginInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{

    /**
     * @see PluginInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'Announcements';
    }

    /**
     * @see PluginInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('Announcements', 'announcements_list', 'message');
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

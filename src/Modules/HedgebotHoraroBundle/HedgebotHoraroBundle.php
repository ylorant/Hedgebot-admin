<?php
namespace App\Modules\HedgebotHoraroBundle;

use App\Service\ApiClientService;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use App\Interfaces\PluginInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;
use App\Modules\HedgebotHoraroBundle\Widget\ScheduleWidget;

class HedgebotHoraroBundle extends Bundle implements PluginInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see PluginInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'Horaro';
    }

    /**
     * @see PluginInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('Horaro', 'horaro_schedule_list', 'date_range');
    }

    /**
     * @param ApiClientService $apiClientService
     * @return ScheduleWidget[]
     * @see PluginInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [
            new ScheduleWidget($apiClientService)
        ];
    }
}

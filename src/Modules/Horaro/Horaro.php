<?php
namespace App\Modules\Horaro;

use App\Service\ApiClientService;
use App\Interfaces\ModuleInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;
use App\Modules\Horaro\Widget\ScheduleWidget;

class Horaro implements ModuleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see ModuleInterface::getModuleName()
     */
    public static function getModuleName()
    {
        return 'Horaro';
    }

    /**
     * @see MenuProviderInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('title.horaro', 'horaro_schedule_list', 'date_range');
    }

    /**
     * @param ApiClientService $apiClientService
     * @return ScheduleWidget[]
     * @see DashboardWidgetsProviderInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [
            new ScheduleWidget($apiClientService)
        ];
    }
}

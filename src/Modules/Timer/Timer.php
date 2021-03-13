<?php
namespace App\Modules\Timer;

use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\ModuleInterface;
use App\Plugin\Menu\MenuItem;
use App\Service\ApiClientService;
use App\Modules\Timer\Widget\TimerWidget;

class Timer implements ModuleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see ModuleInterface::getModuleName()
     */
    public static function getModuleName()
    {
        return 'Timer';
    }

    /**
     * @see MenuProviderInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('title.timers', 'timer_list', 'alarm');
    }

    /**
     * @param ApiClientService|null $apiClientService
     * @return TimerWidget[]
     * @see DashboardWidgetsProviderInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [
            new TimerWidget($apiClientService)
        ];
    }
}

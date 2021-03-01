<?php
namespace App\Modules\HedgebotTimerBundle;

use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\PluginInterface;
use App\Plugin\Menu\MenuItem;
use App\Service\ApiClientService;
use App\Modules\HedgebotTimerBundle\Widget\TimerWidget;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HedgebotTimerBundle extends Bundle implements PluginInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see PluginInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'Timer';
    }

    /**
     * @see PluginInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('Timers', 'timer_list', 'alarm');
    }

    /**
     * @param ApiClientService|null $apiClientService
     * @return TimerWidget[]
     * @see PluginInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [
            new TimerWidget($apiClientService)
        ];
    }
}

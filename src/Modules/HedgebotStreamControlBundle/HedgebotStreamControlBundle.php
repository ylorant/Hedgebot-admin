<?php
namespace App\Modules\HedgebotStreamControlBundle;

use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\PluginInterface;
use App\Service\ApiClientService;
use App\Modules\HedgebotStreamControlBundle\Widget\StreamSettingsWidget;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HedgebotStreamControlBundle extends Bundle implements PluginInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see PluginInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'StreamControl';
    }

    /**
     * @see PluginInterface::getMenu()
     */
    public function getMenu()
    {
        return null;
    }

    /**
     * @param ApiClientService $apiClientService
     * @return StreamSettingsWidget[]
     * @see PluginInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [
            new StreamSettingsWidget($apiClientService)
        ];
    }
}

<?php
namespace App;

use App\Service\ApiClientService;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use App\Interfaces\MenuProviderInterface;
use App\Plugin\Menu\MenuItemList;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Widget\ChatWidget\ChatWidget;
use App\Widget\DefaultWidget\DefaultWidget;
use App\Widget\CustomCallWidget\CustomCallWidget;

class HedgebotCoreBundle extends Bundle implements MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * Settings menu provider for the bot
     */
    public function getMenu()
    {
        $baseItem = new MenuItemList();

        // Sub-menu
        $baseItem
            ->header('Core')->end()
            ->item('Dashboard', 'dashboard', 'dashboard')->end()
            ->item('Permissions', 'security_index', 'lock')->end()
            ->item('Twitch tokens', 'twitch_index', "zmdi:twitch")->end()
            ->item('Admin settings', null, 'settings')
                ->children()
                    ->item('Widgets', 'settings_widgets')->end()
                    ->item('Custom calls', 'custom_calls_index')->end()
                ->end()
            ->end()
            ->header('Modules')->end()
        ->end();

        return $baseItem;
    }

    /**
     * @param ApiClientService $apiClientService
     * @return array
     * @see PluginInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [
            new ChatWidget(),
            new DefaultWidget(),
            new CustomCallWidget()
        ];
    }

    public static function getDefaultConfig()
    {
        return ['bundles' => [], 'settings' => ['widgets' => []]];
    }
}

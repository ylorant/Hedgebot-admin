<?php
namespace Hedgebot\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItemList;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Widget\ChatWidget\ChatWidget;
use Hedgebot\CoreBundle\Widget\DefaultWidget\DefaultWidget;
use Hedgebot\CoreBundle\Widget\CustomCallWidget\CustomCallWidget;

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
            ->header('Plugins')->end()
        ->end();
        
        return $baseItem;
    }

    /**
     * @see PluginBundleInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets()
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

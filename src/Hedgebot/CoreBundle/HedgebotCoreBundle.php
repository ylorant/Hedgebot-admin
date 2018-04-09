<?php
namespace Hedgebot\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItemList;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Widget\ChatWidget\ChatWidget;

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
            ->item('Dashboard', 'dashboard', 'dashboard')->end()
            ->item('Permissions', 'security_index', 'lock')->end()
            ->item('Services', null, 'settings_remote')
                ->children()
                    ->item('Twitch', 'twitch_index')->end()
                ->end()
            ->end()
            ->item('Settings', null, 'settings')
                ->children()
                    ->item('Widgets', 'settings_widgets')->end()
                ->end()
            ->end()
        ->end();
        
        return $baseItem;
    }

    /**
     * @see PluginBundleInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets()
    {
        return [
            new ChatWidget()
        ];
    }
    
	public static function getDefaultConfig()
	{
	    return ['bundles' => [], 'settings' => ['widgets' => []]];
	}
}
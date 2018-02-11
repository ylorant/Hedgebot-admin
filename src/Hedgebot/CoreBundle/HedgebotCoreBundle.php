<?php
namespace Hedgebot\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItemList;

class HedgebotCoreBundle extends Bundle implements MenuProviderInterface
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
            ->item('Settings', null, 'settings')
                ->children()
                    ->item('Widgets', 'settings_widgets')->end()
                ->end()
            ->end()
            ->item('Other menu', null, 'account_circle')->end()
        ->end();
        
        return $baseItem;
    }
    
	public static function getDefaultConfig()
	{
	    return ['bundles' => [], 'settings' => ['widgets' => []]];
	}
}
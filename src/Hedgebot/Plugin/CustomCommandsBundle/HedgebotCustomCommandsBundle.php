<?php
namespace Hedgebot\Plugin\CustomCommandsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItem;
use Hedgebot\Plugin\CustomCommandsBundle\Widget\SampleWidget\SampleWidget;
use Hedgebot\Plugin\CustomCommandsBundle\Widget\SecondSampleWidget\SecondSampleWidget;

class HedgebotCustomCommandsBundle extends Bundle implements PluginBundleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    
    /**
     * @see PluginBundleInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'CustomCommands';
    }
    
    /**
     * @see PluginBundleInterface::getMenu()
     */
    public function getMenu()
    {
        $baseItem = new MenuItem('Custom commands', null, 'list');
        
        // Sub-menu
        $baseItem
            ->children()
                ->item('Sub-item 1', 'custom_commands_list', null)->end()
                ->item('Sub-item 2')->end()
            ->end();
        
        return $baseItem;
    }
    
    /**
     * @see PluginBundleInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets()
    {
        return [
            new SampleWidget(),
            new SecondSampleWidget()
        ];
    }
}
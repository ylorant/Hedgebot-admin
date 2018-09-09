<?php
namespace Hedgebot\Plugin\AnnouncementsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItem;

class HedgebotAnnouncementsBundle extends Bundle implements PluginBundleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    
    /**
     * @see PluginBundleInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'Announcements';
    }
    
    /**
     * @see PluginBundleInterface::getMenu()
     */
    public function getMenu()
    {
        $baseItem = new MenuItem('Announcements', 'announcements_list', 'list');
        
        return $baseItem;
    }
    
    /**
     * @see PluginBundleInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets()
    {
        return [];
    }
}

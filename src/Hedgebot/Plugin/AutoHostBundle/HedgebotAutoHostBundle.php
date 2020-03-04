<?php
namespace Hedgebot\Plugin\AutoHostBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItem;


class HedgebotAutoHostBundle extends Bundle implements PluginBundleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{

    /**
     * @see PluginBundleInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'AutoHost';
    }

    /**
     * @see PluginBundleInterface::getMenu()
     */
    public function getMenu()
    {
        return new MenuItem('AutoHost', 'autohost_list', 'live_tv');
    }

    /**
     * @see PluginBundleInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets()
    {
        return [];
    }
}

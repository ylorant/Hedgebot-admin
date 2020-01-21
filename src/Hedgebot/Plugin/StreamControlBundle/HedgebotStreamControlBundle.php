<?php
namespace Hedgebot\Plugin\StreamControlBundle;

use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use Hedgebot\Plugin\StreamControlBundle\Widget\StreamSettingsWidget;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HedgebotStreamControlBundle extends Bundle implements PluginBundleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see PluginBundleInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'StreamControl';
    }
    
    /**
     * @see PluginBundleInterface::getMenu()
     */
    public function getMenu()
    {
        return null;
    }
    
    /**
     * @see PluginBundleInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets()
    {
        $hedgebotApi = $this->container->get('hedgebot_api');

        return [
            new StreamSettingsWidget($hedgebotApi)
        ];
    }
}
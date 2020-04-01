<?php
namespace Hedgebot\Plugin\TimerBundle;

use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use Hedgebot\Plugin\TimerBundle\Widget\TimerWidget;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HedgebotTimerBundle extends Bundle implements PluginBundleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see PluginBundleInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'Timer';
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
            new TimerWidget($hedgebotApi)
        ];
    }
}
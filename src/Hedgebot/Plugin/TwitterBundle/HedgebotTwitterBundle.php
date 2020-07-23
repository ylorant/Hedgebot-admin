<?php
namespace Hedgebot\Plugin\TwitterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetsProviderInterface;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItem;

class HedgebotTwitterBundle extends Bundle implements PluginBundleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see PluginBundleInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'Twitter';
    }
    
    /**
     * @see PluginBundleInterface::getMenu()
     */
    public function getMenu()
    {
        $baseItem = new MenuItem('Twitter', null, 'zmdi:twitter');

        $baseItem
            ->children()
                ->item('Accounts', 'twitter_token_list')->end()
                ->item('Scheduled tweets', 'twitter_tweet_list')->end();
        
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
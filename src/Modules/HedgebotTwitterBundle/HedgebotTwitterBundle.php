<?php
namespace App\Modules\HedgebotTwitterBundle;

use App\Service\ApiClientService;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use App\Interfaces\PluginInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class HedgebotTwitterBundle extends Bundle implements PluginInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see PluginInterface::getPluginName()
     */
    public static function getPluginName()
    {
        return 'Twitter';
    }

    /**
     * @see PluginInterface::getMenu()
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
     * @param ApiClientService $apiClientService
     * @return array
     * @see PluginInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [];
    }
}

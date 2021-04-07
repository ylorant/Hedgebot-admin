<?php

namespace App\Modules\Twitter;

use App\Service\ApiClientService;
use App\Interfaces\ModuleInterface;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Plugin\Menu\MenuItem;

class Twitter implements ModuleInterface, MenuProviderInterface, DashboardWidgetsProviderInterface
{
    /**
     * @see ModuleInterface::getModuleName()
     */
    public static function getModuleName()
    {
        return 'Twitter';
    }

    /**
     * @see MenuProviderInterface::getMenu()
     */
    public function getMenu()
    {
        $baseItem = new MenuItem('title.twitter', null, 'zmdi:twitter');
        $baseItem
            ->children()
                ->item('title.accounts', 'twitter_token_list')->end()
                ->item('title.scheduledtweets', 'twitter_tweet_list')->end();
        return $baseItem;
    }

    /**
     * @param ApiClientService $apiClientService
     * @return array
     * @see DashboardWidgetsProviderInterface::getDashboardWidgets()
     */
    public function getDashboardWidgets(ApiClientService $apiClientService): array
    {
        return [];
    }
}

<?php
namespace Hedgebot\CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Hedgebot\CoreBundle\Plugin\Menu\MenuItemList;
use Hedgebot\CoreBundle\Interfaces\MenuProviderInterface;

class MenuGeneratorService
{
    use ContainerAwareTrait;
    
    /**
     * Generates the menu for the different bundles that give a menu.
     *
     * @return MenuItemList
     */
    public function generate()
    {
        // Fetch all menus into a MenuItemList;
        $bundles = $this->container->get('kernel')->getBundles();
        $itemList = new MenuItemList(null);
        
        foreach ($bundles as $bundle) {
            // Keep only bundles that are plugin bundles
            if ($bundle instanceof MenuProviderInterface) {
                $menu = $bundle->getMenu();
                if ($menu instanceof MenuItemList) {
                    foreach ($menu as $item) {
                        $itemList->add($item);
                    }
                } else {
                    $itemList->add($menu);
                }
            }
        }
        
        return $itemList;
    }
}

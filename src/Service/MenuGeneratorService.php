<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use App\Plugin\Menu\MenuItemList;
use App\Interfaces\MenuProviderInterface;
use App\Plugin\Menu\MenuItem;
use Symfony\Component\HttpKernel\KernelInterface;

class MenuGeneratorService
{
    use ContainerAwareTrait;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Generates the menu for the different bundles that give a menu.
     *
     * @return MenuItemList
     */
    public function generate()
    {
        // Fetch all menus into a MenuItemList;
        $bundles = $this->kernel->getBundles();
        $itemList = new MenuItemList(null);

        foreach ($bundles as $bundle) {
            // Keep only bundles that are plugin bundles
            if ($bundle instanceof MenuProviderInterface) {
                $menu = $bundle->getMenu();
                if ($menu instanceof MenuItemList) {
                    foreach ($menu as $item) {
                        $itemList->add($item);
                    }
                } elseif ($menu instanceof MenuItem) {
                    $itemList->add($menu);
                }
            }
        }

        return $itemList;
    }
}

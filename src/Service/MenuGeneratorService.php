<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use App\Plugin\Menu\MenuItemList;
use App\Interfaces\MenuProviderInterface;
use App\Plugin\Menu\MenuItem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuGeneratorService
{
    use ContainerAwareTrait;

    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     */
    public function __construct(KernelInterface $kernel, TranslatorInterface $translator)
    {
        $this->kernel = $kernel;
        $this->translator = $translator;
    }

    /**
     * Generates the menu for the different bundles that give a menu.
     *
     * @return MenuItemList
     */
    public function generate(): MenuItemList
    {
        // Fetch all menus into a MenuItemList;
        $bundles = $this->kernel->getBundles();
        $itemList = $this->getDefaultMenuItems();

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

    /**
     * @return MenuItemList
     */
    public function getDefaultMenuItems(): MenuItemList
    {
        $baseItem = new MenuItemList();

        // Sub-menu
        $baseItem
            ->header('Core')->end()
            ->item($this->translator->trans('title.dashboard'), 'dashboard', 'dashboard')->end()
            ->item($this->translator->trans('title.permissions'), 'security_index', 'lock')->end()
            ->item($this->translator->trans('title.twitch'), 'twitch_index', "zmdi:twitch")->end()
            ->item($this->translator->trans('title.settings'), null, 'settings')
            ->children()
            ->item('Widgets', 'settings_widgets')->end()
            ->item('Custom calls', 'custom_calls_index')->end()
            ->end()
            ->end()
            ->header('Modules')->end()
            ->end();

        return $baseItem;
    }
}

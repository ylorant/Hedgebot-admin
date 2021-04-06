<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use App\Plugin\Menu\MenuItemList;
use App\Interfaces\MenuProviderInterface;
use App\Interfaces\ModuleInterface;
use App\Plugin\Menu\MenuItem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuGeneratorService
{
    use ContainerAwareTrait;

    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     * @param Security $security
     * @param TranslatorInterface $translator
     */
    public function __construct(KernelInterface $kernel, Security $security, TranslatorInterface $translator)
    {
        $this->kernel = $kernel;
        $this->security = $security;
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
        $itemList = $this->getDefaultMenuItems();
        $modules = $this->getActivatedModules();

        foreach ($modules as $module) {
            $object = new $module();
            // Keep only objects that are modules
            if ($object instanceof MenuProviderInterface) {
                $menu = $object->getMenu();
                if ($menu instanceof MenuItemList) {
                    foreach ($menu as $item) {
                        $this->processMenuItem($item, $object);
                        $itemList->add($item);
                    }
                } elseif ($menu instanceof MenuItem) {
                    $this->processMenuItem($menu, $object);
                    $itemList->add($menu);
                }
            }
        }

        return $itemList;
    }

    /**
     * Processes a menu item's properties, to prepare it for inclusion in the global
     * menu object. It handles the menu title translation domain for example.
     * If the menu has a submenu, it will process its children too.
     * 
     * @param MenuItem $menuItem The menu to process.
     * @param MenuProviderInterface $menuProvider The provider that provided the menu.
     * @return void 
     */
    protected function processMenuItem(MenuItem $menuItem, MenuProviderInterface $menuProvider)
    {
        $translationDomain = null;
        if($menuProvider instanceof ModuleInterface) {
            $translationDomain = $menuProvider->getModuleName();
        }

        $menuItem->setTitle(
            $this->translator->trans($menuItem->getTitle(), [], strtolower($translationDomain))
        );

        if(!empty($menuItem->getSubmenu()) && !empty($menuItem->getSubmenu()->count())) {
            foreach($menuItem->getSubmenu() as $subMenuItem) {
                $this->processMenuItem($subMenuItem, $menuProvider);
            }
        }
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
            ->item($this->translator->trans('title.dashboard'), 'dashboard', 'dashboard')->end();

        if ($this->security->isGranted(User::ROLE_ADMIN)) {
            $baseItem
                ->item($this->translator->trans('title.web_permissions'), null, 'lock')
                    ->children()
                        ->item($this->translator->trans('title.users'), 'users_index')->end()
                        ->item($this->translator->trans('title.roles'), 'users_roles')->end()
                    ->end()
                ->end();
        }

        $baseItem
            ->item($this->translator->trans('title.bot_permissions'), 'permissions_index', 'lock')->end()
            ->item($this->translator->trans('title.twitch_api'), 'twitch_index', "zmdi:twitch")->end()
            ->item($this->translator->trans('title.settings'), null, 'settings')
                ->children()
                    ->item($this->translator->trans('title.widgets'), 'settings_widgets')->end()
                    ->item($this->translator->trans('title.customcalls'), 'custom_calls_index')->end()
                ->end()
            ->end()
            ->header('Modules')->end()
            ->end();

        return $baseItem;
    }

    /**
     * @TODO Redundant with DashboardWidgetManagerService. Put in in another class ? helper ?
     *
     * @return array
     */
    public function getActivatedModules(): array
    {
        $activatedModules = [];
        // Load active modules routes
        $modulesList = new FileResource($this->kernel->getProjectDir() . '/config/hedgebot.yaml');

        if (is_file($modulesList)) {
            $yamlContent = Yaml::parse(file_get_contents($modulesList));
            if (!empty($yamlContent['modules'])) {
                foreach ($yamlContent['modules'] as $moduleName => $module) {
                    $activatedModules[] = $module;
                    //$activatedModules[] = $module::class; //only for PHP 8+
                }
            }
        }

        return $activatedModules;
    }
}

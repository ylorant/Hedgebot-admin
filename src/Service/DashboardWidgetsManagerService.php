<?php
namespace App\Service;

use App\Interfaces\DashboardWidgetsProviderInterface;
use App\Widget\ChatWidget\ChatWidget;
use App\Widget\CustomCallWidget\CustomCallWidget;
use App\Widget\DefaultWidget\DefaultWidget;
use Exception;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Ramsey\Uuid\Uuid;

/**
 * Dashboard widgets manager service.
 * This service handles dashboard widgets, allowing templates to show them.
 */
class DashboardWidgetsManagerService
{
    use ContainerAwareTrait;

    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var ApiClientService
     */
    private $apiClientService;
    /**
     * @var FileLocator
     */
    private $fileLocator;
    /**
     * @var string
     * Config file path for the widgets
     */
    protected $layoutPath;
    /**
     * @var array|null
     * Layouts cache, to avoid reading them more than once per page load
     */
    protected $layoutCache;

    const DEFAULT_LAYOUT = "default";


    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     * @param ApiClientService $apiClientService
     * @param FileLocator $fileLocator
     * @param string $layoutPath The path to the configuration to use (containing layouts).
     */
    public function __construct(
        KernelInterface $kernel,
        ApiClientService $apiClientService,
        FileLocator $fileLocator,
        string $layoutPath
    ) {
        $this->kernel = $kernel;
        $this->apiClientService = $apiClientService;
        $this->fileLocator = $fileLocator;
        $this->layoutPath = $layoutPath;
        $this->layoutCache = null;
    }

    /**
     * Gets all the available widgets, for all the bundles
     */
    public function getAvailableWidgets(): array
    {
        $availableWidgets = $this->getDefaultWidgets();
        $modules = $this->getActivatedModules();

        foreach ($modules as $module) {
            $object = new $module();
            // Keep only bundles that are plugin bundles
            if ($object instanceof DashboardWidgetsProviderInterface) {
                $availableWidgets = array_merge(
                    $availableWidgets,
                    $object->getDashboardWidgets($this->apiClientService)
                );
            }
        }

        return $availableWidgets;
    }

    /**
     * @TODO Redundant with MenuGeneratorService. Put in in another class ? helper ?
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

    /**
     * Return widgets always loaded
     *
     * @return array
     */
    public function getDefaultWidgets(): array
    {
        return [
            new ChatWidget(),
            new DefaultWidget(),
            new CustomCallWidget()
        ];
    }

    /**
     * Get a widget by its name.
     *
     * @param $name
     * @return mixed|null
     */
    public function getWidgetByName($name)
    {
        $widgetsList = $this->getAvailableWidgets();

        foreach ($widgetsList as $widget) {
            if ($widget->getId() == $name) {
                return $widget;
            }
        }

        return null;
    }

    /**
     * Gets a layout by its identifier.
     *
     * @param string $id The layout to fetch's ID.
     * @return array|null The layout model if found, null otherwise.
     */
    public function getLayoutById(string $id)
    {
        $layouts = $this->getLayouts();

        if (isset($layouts[$id])) {
            return $layouts[$id];
        }

        return null;
    }

    /**
     * Gets the list of available layouts from their config file.
     * Default config file location is in Resources/config/layouts.yaml. Refer to it for how to make new layouts.
     *
     * @return array The list of available layouts.
     */
    public function getLayouts()
    {
        if (empty($this->layoutCache)) {
            $layoutPath = $this->fileLocator->locate($this->layoutPath);

            $layoutConfig = Yaml::parse(file_get_contents($layoutPath));
            $this->layoutCache = $layoutConfig['layouts'];
        }

        return $this->layoutCache;
    }

    /**
     * Generates a widget unique ID. Uses UUIDv4.
     *
     * @return string The unique generated widget ID.
     * @throws Exception
     */
    public function generateWidgetID(): string
    {
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }
}

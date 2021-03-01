<?php
namespace App\Service;

use App\Interfaces\DashboardWidgetsProviderInterface;
use Exception;
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
        $bundles = $this->kernel->getBundles();
        $availableWidgets = [];

        foreach ($bundles as $bundle) {
            // Keep only bundles that are plugin bundles
            if ($bundle instanceof DashboardWidgetsProviderInterface) {
                $availableWidgets = array_merge(
                    $availableWidgets,
                    $bundle->getDashboardWidgets($this->apiClientService)
                );
            }
        }

        return $availableWidgets;
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
    public function getLayoutById($id)
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
    public function generateWidgetID()
    {
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }
}

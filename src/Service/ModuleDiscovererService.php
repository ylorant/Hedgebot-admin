<?php
namespace App\Service;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use App\Routing\ModuleRouteLoader;
use App\Interfaces\PluginInterface;

class ModuleDiscovererService
{
    protected $apiClient;
    protected $apiConfigPath;
    protected $clearCache;
    protected $bundlesModulesToLoad;

    use ContainerAwareTrait;

    public function __construct(ApiClientService $apiClient, $apiConfigPath)
    {
        $this->apiClient = $apiClient;
        $this->apiConfigPath = new FileResource($apiConfigPath);
        $this->clearCache = false;
        $this->bundlesModulesToLoad = [];
    }

    /**
     * Display modules depends of modules activated into Hedgebot
     *
     * @return bool
     */
    public function discoverModules(): bool
    {
        $modulesBundles = [];

        // Getting all plugins' bundles
        $finder = new Finder();
        $modulesPath = DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . ModuleRouteLoader::MODULES_NAMESPACE;
        $directory = $this->container->get('kernel')->getProjectDir()
            . $modulesPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR;
        $files = $finder->in($directory)->files()->name('*Bundle.php');

        foreach ($files as $file) {
            $filePath = str_replace(DIRECTORY_SEPARATOR, "/", $file->getPath());
            $pathParts = explode("/", trim($filePath, "/"));
            $bundleName = end($pathParts);

            $class = 'App\\' . ModuleRouteLoader::MODULES_NAMESPACE . '\\' . $bundleName . '\\'
                . $file->getBasename('.php');
            if (is_subclass_of($class, PluginInterface::class)) {
                $module = $class::getPluginName();
                $modulesBundles[$module] = $class;
            }
        }

        // Getting module list from the bot
        $endpoint = $this->apiClient->endpoint('/plugin');
        $loadedModules = $endpoint->getList();

        // Filtering module classes to keep only the enabled ones to load
        $filterFunction = function ($module) use ($loadedModules) {
            return in_array($module, $loadedModules);
        };


        $this->bundlesModulesToLoad = array_filter($modulesBundles, $filterFunction, ARRAY_FILTER_USE_KEY);

        // Updating the config with the new bundles
        if (is_file($this->apiConfigPath)) {
            $config = Yaml::parse(file_get_contents($this->apiConfigPath));
        } else {
            $config = ['bundles' => []];
        }

        $config['bundles'] = $this->bundlesModulesToLoad;
        $yaml = Yaml::dump($config);
        file_put_contents($this->apiConfigPath, $yaml);

        return true;
    }

    /**
     * Schedules a cache clear at page execution end to force route reloading.
     */
    public function scheduleCacheClear()
    {
        $this->clearCache = true;
    }

    /**
     * Event called on kernel termination, will clear the cache if asked by someone
     * (controller, another event listener...).
     *
     * @param TerminateEvent $event The event given by the controller.
     */
    public function onKernelTerminate(TerminateEvent $event)
    {
        if ($this->clearCache) {
            $fs = $this->container->get('filesystem');
            $fs->remove($this->container->getParameter('kernel.cache_dir'));
        }
    }

    /**
     * Event listener on login success, commanding to reload modules definitions.
     */
    public function onLoginSuccess()
    {
        $this->discoverModules();
        $this->scheduleCacheClear();
    }
}

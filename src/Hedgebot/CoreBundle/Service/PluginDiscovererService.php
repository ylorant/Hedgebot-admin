<?php
namespace Hedgebot\CoreBundle\Service;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Hedgebot\CoreBundle\Routing\PluginRouteLoader;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use Hedgebot\CoreBundle\Exception\RPCException;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class PluginDiscovererService
{
    protected $apiClient;
    protected $configPath;
    protected $clearCache;
    
    use ContainerAwareTrait;
    
    public function __construct(ApiClientService $apiClient, $configPath)
    {
        $this->apiClient = $apiClient;
        $this->configPath = $configPath;
        $this->clearCache = false;
    }
    
    public function discoverPlugins()
    {
        $pluginsBundles = [];
        $bundlesPluginsToLoad = [];
        
        // Getting all plugins' bundles
        $finder = new Finder();
        $pluginsPath = str_replace('\\', '/', PluginRouteLoader::PLUGINS_NAMESPACE);
        $directory = $this->container->get('kernel')->getProjectDir(). '/src/'. $pluginsPath. '/*/';
        $files = $finder->in($directory)->files()->name('*Bundle.php');
        
        foreach ($files as $file) {
            $filePath = str_replace(DIRECTORY_SEPARATOR, "/", $file->getPath());
            $pathParts = explode("/", trim($filePath, "/"));
            $bundleName = end($pathParts);
            
            $class = PluginRouteLoader::PLUGINS_NAMESPACE. '\\'. $bundleName. '\\'. $file->getBasename('.php');
            if (is_subclass_of($class, PluginBundleInterface::class)) {
                $plugin = $class::getPluginName();
                $pluginsBundles[$plugin] = $class;
            }
        }
        
        // Getting plugin list from the bot
        $endpoint = $this->apiClient->endpoint('/plugin');
        $loadedPlugins = $endpoint->getList();

        // Filtering plugin classes to keep only the enabled ones to load
        $filterFunction = function ($plugin) use ($loadedPlugins) {
            return in_array($plugin, $loadedPlugins);
        };

        
        $bundlesPluginsToLoad = array_filter($pluginsBundles, $filterFunction, ARRAY_FILTER_USE_KEY);
        
        // Updating the config with the new bundles
        if (is_file($this->configPath)) {
            $config = Yaml::parse(file_get_contents($this->configPath));
        } else {
            $config = ['bundles' => []];
        }
        
        $config['bundles'] = $bundlesPluginsToLoad;
        $yaml = Yaml::dump($config);
        file_put_contents($this->configPath, $yaml);
        
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
     * Event called on kernel termination, will clear the cache if asked by someone (controller, another event listener...).
     *
     * @param PostResponseEvent $event The event given by the controller.
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        if ($this->clearCache) {
            $fs = $this->container->get('filesystem');
            $fs->remove($this->container->getParameter('kernel.cache_dir'));
        }
    }
    
    /**
     * Event listener on login success, commanding to reload plugins definitions.
     */
    public function onLoginSuccess()
    {
        $this->discoverPlugins();
        $this->scheduleCacheClear();
    }
}

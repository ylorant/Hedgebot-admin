<?php
namespace Hedgebot\CoreBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;

class PluginRouteLoader extends Loader
{
    private $bundles;
    private $kernel;
    
    const PLUGINS_NAMESPACE = "Hedgebot\\Plugin";
    
    public function __construct($kernel, $bundles)
    {
        $this->bundles = $bundles;
        $this->kernel = $kernel;
    }
    
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();
        
        // Checking each class namespace
        foreach ($this->bundles as $bundle) {
            if (is_subclass_of($bundle, PluginBundleInterface::class)) {
                $bundleParts = explode('\\', $bundle);
                $class = end($bundleParts);
                $resource = '@'. $class. '/Resources/config/routing.yml';
                $resourceFile = $this->kernel->locateResource($resource);
                $type = 'yaml';
                
                if (is_file($resourceFile)) {
                    $importedRoutes = $this->import($resource, $type);
                    $collection->addCollection($importedRoutes);
                }
            }
        }
        
        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'plugin_routes' === $type;
    }
}

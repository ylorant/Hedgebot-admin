<?php
namespace Hedgebot\CoreBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Hedgebot\CoreBundle\Interfaces\PluginBundleInterface;
use InvalidArgumentException;

abstract class AbstractPluginRouteLoader extends Loader
{
    private $bundles;
    private $kernel;
    
    const PLUGINS_NAMESPACE = "Hedgebot\\Plugin";
    const ROUTING_YAML = null;
    
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
                $resource = '@'. $class. static::ROUTING_YAML;
                $type = 'yaml';
                $resourceFile = null;

                // Try to locate the resource but don't crash if it doesn't exist
                try {
                    $resourceFile = $this->kernel->locateResource($resource);
                } catch(InvalidArgumentException $e) {}
                
                if (is_file($resourceFile)) {
                    $importedRoutes = $this->import($resource, $type);
                    $collection->addCollection($importedRoutes);
                }
            }
        }
        
        return $collection;
    }
}

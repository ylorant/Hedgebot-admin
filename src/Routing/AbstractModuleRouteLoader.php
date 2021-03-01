<?php
namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use App\Interfaces\PluginInterface;
use InvalidArgumentException;

abstract class AbstractModuleRouteLoader extends Loader
{
    private $bundles;
    private $kernel;

    const MODULES_NAMESPACE = "Modules";
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
            if (is_subclass_of($bundle, PluginInterface::class)) {
                $bundleParts = explode('\\', $bundle);
                $class = end($bundleParts);
                $resource = '@'. $class. static::ROUTING_YAML;
                $type = 'yaml';
                $resourceFile = null;

                // Try to locate the resource but don't crash if it doesn't exist
                try {
                    $resourceFile = $this->kernel->locateResource($resource);
                } catch (InvalidArgumentException $e) {
                }

                if (is_file($resourceFile)) {
                    $importedRoutes = $this->import($resource, $type);
                    $collection->addCollection($importedRoutes);
                }
            }
        }

        return $collection;
    }
}

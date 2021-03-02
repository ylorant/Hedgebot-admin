<?php
namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use App\Interfaces\ModuleInterface;
use InvalidArgumentException;

abstract class AbstractModuleRouteLoader extends Loader
{
    private $modules;
    private $kernel;

    const MODULES_NAMESPACE = "Modules";
    const ROUTING_YAML = null;

    public function __construct($kernel, $modules)
    {
        $this->modules = $modules;
        $this->kernel = $kernel;
    }

    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        // Checking each class namespace
        foreach ($this->modules as $module) {
            if (is_subclass_of($module, ModuleInterface::class)) {
                $moduleParts = explode('\\', $module);
                $class = end($moduleParts);
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

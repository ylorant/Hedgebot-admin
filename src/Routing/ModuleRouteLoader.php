<?php
namespace App\Routing;

class ModuleRouteLoader extends AbstractModuleRouteLoader
{
    const ROUTING_YAML = '/config/routes.yaml';

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return 'plugin_routes' === $type;
    }
}

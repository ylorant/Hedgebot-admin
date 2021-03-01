<?php
namespace App\Routing;

class ModulePublicRouteLoader extends AbstractModuleRouteLoader
{
    const ROUTING_YAML = '/config/routes.yaml';

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return 'plugin_public_routes' === $type;
    }
}

<?php

namespace App\Routing;

class ModuleRouteLoader extends AbstractModuleRouteLoader
{
    protected const ROUTING_YAML = '/config/routes.yaml';

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null): bool
    {
        return 'plugin_routes' === $type;
    }
}

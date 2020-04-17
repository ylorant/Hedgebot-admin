<?php
namespace Hedgebot\CoreBundle\Routing;

class PluginRouteLoader extends AbstractPluginRouteLoader
{
    const ROUTING_YAML = '/Resources/config/routing.yml';

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return 'plugin_routes' === $type;
    }
}
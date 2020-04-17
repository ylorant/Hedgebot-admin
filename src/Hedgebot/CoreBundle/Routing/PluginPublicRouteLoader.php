<?php
namespace Hedgebot\CoreBundle\Routing;

class PluginPublicRouteLoader extends AbstractPluginRouteLoader
{
    const ROUTING_YAML = '/Resources/config/routing_public.yml';

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return 'plugin_public_routes' === $type;
    }
}
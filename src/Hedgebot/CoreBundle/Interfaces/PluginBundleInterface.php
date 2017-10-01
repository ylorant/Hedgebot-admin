<?php
namespace Hedgebot\CoreBundle\Interfaces;

interface PluginBundleInterface
{
    /**
     * Returns the plugin name for which the bundles provides the interface.
     * 
     * @return string The plugin name.
     */
    public static function getPluginName();
}
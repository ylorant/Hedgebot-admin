<?php
namespace App\Interfaces;

interface PluginInterface
{
    /**
     * Returns the plugin name for which the bundles provides the interface.
     *
     * @return string The plugin name.
     */
    public static function getPluginName();
}

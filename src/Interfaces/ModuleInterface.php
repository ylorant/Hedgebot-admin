<?php
namespace App\Interfaces;

interface ModuleInterface
{
    /**
     * Returns the module name for which the bundles provides the interface.
     *
     * @return string The plugin name.
     */
    public static function getModuleName();
}

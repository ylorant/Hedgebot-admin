<?php
namespace App\Interfaces;

interface MenuProviderInterface
{
    /**
     * Returns the menu item for the plugin bundle.
     *
     * @return MenuItem|null The menu item for the plugin.
     */
    public function getMenu();
}

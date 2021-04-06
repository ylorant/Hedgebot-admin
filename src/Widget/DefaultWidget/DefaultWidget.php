<?php
namespace App\Widget\DefaultWidget;

use App\Interfaces\DashboardWidgetInterface;

/**
 * Default widget for the admin interface.
 * This widget is the one that will be instancied by default when there is no dashboard layout defined in the settings.
 */
class DefaultWidget implements DashboardWidgetInterface
{
    public function getId()
    {
        return 'default-widget';
    }

    public function getName()
    {
        return 'Default widget';
    }

    public function getDescription()
    {
        return "Shows a default message when the user doesn't have any dashboard layout set.";
    }

    public function getViewName()
    {
        return 'core/widget/default-widget.html.twig';
    }

    public function getScriptPaths()
    {
        return [];
    }

    public function getSettingsFormType()
    {
        return null;
    }

    public function update(array $settings = [])
    {
        return [];
    }
}

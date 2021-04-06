<?php
namespace App\Widget\CustomCallWidget;

use App\Interfaces\DashboardWidgetInterface;

class CustomCallWidget implements DashboardWidgetInterface
{
    public function getId()
    {
        return 'custom-call';
    }

    public function getName()
    {
        return "Custom call button";
    }

    public function getDescription()
    {
        return "Provides a button to perform a pre-set HTTP call";
    }

    public function getViewName()
    {
        return 'core/widget/custom-call-widget.html.twig';
    }

    public function getScriptPaths()
    {
        return [];
    }

    public function getSettingsFormType()
    {
        return CustomCallWidgetSettingsType::class;
    }

    public function update(array $settings = [])
    {
        return [
            'settings' => $settings
        ];
    }
}

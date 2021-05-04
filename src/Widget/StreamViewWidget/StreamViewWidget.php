<?php
namespace App\Widget\StreamViewWidget;

use App\Interfaces\DashboardWidgetInterface;

class StreamViewWidget implements DashboardWidgetInterface
{
    public function getId()
    {
        return 'stream-view';
    }

    public function getName()
    {
        return "Stream view widget";
    }

    public function getDescription()
    {
        return "Shows the specified Twitch channel stream on the dashboard";
    }

    public function getViewName()
    {
        return 'core/widget/stream-view-widget.html.twig';
    }

    public function getScriptPaths()
    {
        return [];
    }

    public function getSettingsFormType()
    {
        return StreamViewWidgetSettingsType::class;
    }

    public function update(array $settings = [])
    {
        return [
            'settings' => $settings
        ];
    }
}

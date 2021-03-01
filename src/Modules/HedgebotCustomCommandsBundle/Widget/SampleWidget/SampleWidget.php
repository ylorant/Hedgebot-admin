<?php
namespace App\Modules\HedgebotCustomCommandsBundle\Widget\SampleWidget;

use App\Interfaces\DashboardWidgetInterface;
use App\HedgebotCustomCommandsBundle\Widget\SampleWidget\SampleWidgetSettingsType;
use DateTime;

class SampleWidget implements DashboardWidgetInterface
{
    protected $time;

    public function getId()
    {
        return 'customcommands-sample';
    }

    public function getName()
    {
        return 'Custom commands Sample Widget';
    }

    public function getDescription()
    {
        return 'This is a sample widget from the custom commands plugin. It has been made to test the widget system.';
    }

    public function getViewName()
    {
        return 'customcommands/widget/sample-widget.html.twig';
    }

    public function getScriptPaths()
    {
        return [];
    }

    public function getSettingsFormType()
    {
        return SampleWidgetSettingsType::class;
    }

    public function update(array $settings = [])
    {
        return [
            'time' => (new DateTime())->format('H:i:s'),
            'settings' => $settings
        ];
    }
}

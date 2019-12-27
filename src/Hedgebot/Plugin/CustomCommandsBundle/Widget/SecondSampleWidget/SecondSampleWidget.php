<?php
namespace Hedgebot\Plugin\CustomCommandsBundle\Widget\SecondSampleWidget;

use Hedgebot\CoreBundle\Interfaces\DashboardWidgetInterface;
use DateTime;

class SecondSampleWidget implements DashboardWidgetInterface
{
    protected $time;

    public function getId()
    {
        return 'customcommands-second-sample';
    }

    public function getName()
    {
        return 'Custom commands Second Sample Widget';
    }

    public function getDescription()
    {
        return 'This is the second test widget.';
    }

    public function getViewName()
    {
        return 'HedgebotCustomCommandsBundle:widget:second-sample-widget.html.twig';
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

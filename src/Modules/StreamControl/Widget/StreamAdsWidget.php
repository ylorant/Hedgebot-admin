<?php
namespace App\Modules\StreamControl\Widget;

use App\Interfaces\DashboardWidgetInterface;

class StreamAdsWidget implements DashboardWidgetInterface
{
    /**
     * @inheritDoc
     */
    public function getId()
    {
        return "stream-ads";
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return "Stream ads control";
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return "Allows to start ads on the specified Twitch channel.";
    }

    /**
     * @inheritDoc
     */
    public function getViewName()
    {
        return 'streamcontrol/widget/stream-ads.html.twig';
    }

    /**
     * @inheritDoc
     */
    public function getScriptPaths()
    {
        return [
            'js/modules/plugin/widget/streamcontrol.js'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsFormType()
    {
        return StreamSettingsWidgetSettingsType::class;
    }

    /**
     * @inheritDoc
     */
    public function update(array $settings = [])
    {
        return [
            'settings' => $settings
        ];
    }
}
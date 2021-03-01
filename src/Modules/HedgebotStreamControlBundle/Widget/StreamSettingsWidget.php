<?php
namespace App\Modules\HedgebotStreamControlBundle\Widget;

use App\Interfaces\DashboardWidgetInterface;
use App\Service\ApiClientService;

class StreamSettingsWidget implements DashboardWidgetInterface
{
    /** @var ApiClientService $hedgebotApi */
    protected $hedgebotApi;

    public function __construct(ApiClientService $hedgebotApi)
    {
        $this->hedgebotApi = $hedgebotApi;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return "stream-settings";
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return "Stream settings control";
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return "Allows controlling the Twitch settings such as title and game name on the selected channel.";
    }

    /**
     * @inheritDoc
     */
    public function getViewName()
    {
        return 'streamcontrol/widget/stream-settings.html.twig';
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
        $endpoint = $this->hedgebotApi->endpoint('/plugin/streamcontrol');
        $currentInfo = $endpoint->getChannelInfo($settings['channel']);

        return [
            'settings' => $settings,
            'info' => $currentInfo
        ];
    }
}

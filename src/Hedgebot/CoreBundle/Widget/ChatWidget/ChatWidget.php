<?php
namespace Hedgebot\CoreBundle\Widget\ChatWidget;

use Hedgebot\CoreBundle\Interfaces\DashboardWidgetInterface;
use DateTime;

/**
 * Twitch chat widget for the admin interface.
 * This widget simply inserts an iframe pointing to the Twitch chat page for the given channel on the dashboard.
 */
class ChatWidget implements DashboardWidgetInterface
{
    public function getId()
    {
        return 'twitch-chat';
    }

    public function getName()
    {
        return 'Twitch Chat widget';
    }

    public function getDescription()
    {
        return 'Shows the Twitch chat for the given channel.';
    }

    public function getViewName()
    {
        return 'HedgebotCoreBundle:widget:chat-widget.html.twig';
    }

    public function getSettingsFormType()
    {
        return ChatWidgetSettingsType::class;
    }

    public function update(array $settings = [])
    {
        return [
            'settings' => $settings
        ];
    }
}

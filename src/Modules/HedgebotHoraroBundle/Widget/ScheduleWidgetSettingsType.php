<?php
namespace App\Modules\HedgebotHoraroBundle\Widget;

use App\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ScheduleWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get the bot channels
        $hedgebotApi = $options['hedgebot_api'];
        $serverEndpoint = $hedgebotApi->endpoint('/server');
        $channels = $serverEndpoint->getAvailableChannels();

        $channelsOption = [];
        foreach ($channels as $channel) {
            $channelsOption[$channel] = $channel;
        }

        $builder
            ->add('channel', ChoiceType::class, [
                'choices' => $channelsOption
            ]);
    }
}

<?php
namespace Hedgebot\Plugin\TimerBundle\Widget;

use Hedgebot\CoreBundle\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class TimerWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get the bot channels
        $hedgebotApi = $options['hedgebot_api'];
        $timerEndpoint = $hedgebotApi->endpoint('/plugin/timer');
        $timers = $timerEndpoint->getTimers();

        $timersOption = [];
        foreach($timers as $timer) {
            $timersOption[$timer->id] = $timer->id;
        }

        $builder
            ->add('timer', ChoiceType::class, [
                'choices' => $timersOption
            ])
            ->add('background_color', ChoiceType::class, [
                'choices' => array_flip(TimerWidget::COLORS),
                'required' => false
            ]);
    }
}
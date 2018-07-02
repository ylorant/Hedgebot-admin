<?php
namespace Hedgebot\Plugin\HoraroBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('channels');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Preparing the channel list to be used in the choice type
        $channels = ['' => '-- No channel specified --'];
        foreach ($options['channels'] as $channel) {
            $channels[$channel] = $channel;
        }

        $builder
            ->add('eventId', TextType::class, ['label' => "Event ID", "disabled" => true])
            ->add('scheduleId', TextType::class, ['label' => "Schedule ID", "disabled" => true])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Schedule is enabled',
                'required' => false
            ])
            ->add('channel', ChoiceType::class, [
                'label' => 'Channel',
                'expanded' => false,
                'choices' => $channels
            ])
            ->add('titleTemplate', TextType::class, ['label' => 'Title template', 'required' => false])
            ->add('gameTemplate', TextType::class, ['label' => 'Game name template', 'required' => false])
            ->add('announceTemplate', TextType::class, ['label' => 'Announce message template', 'required' => false])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn-lg btn-primary waves-effect'
                ]
            ]);
    }
}
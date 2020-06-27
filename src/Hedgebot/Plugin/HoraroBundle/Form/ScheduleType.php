<?php
namespace Hedgebot\Plugin\HoraroBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ScheduleType extends AbstractType
{
    const SOURCE_NAMESPACE = "Horaro";
    const CURRENT_DATA_SOURCE_PATH = "schedule.currentItem.data";
    const NEXT_DATA_SOURCE_PATH = "schedule.nextItem.data";

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
            ->add('identSlug', HiddenType::class, [
                'label' => "Event ID",
                "disabled" => true,
                'attr' => [
                    'class' => 'ident-slug'
                ]])
            ->add('eventId', TextType::class, ['label' => "Event ID", "disabled" => true])
            ->add('scheduleId', TextType::class, ['label' => "Schedule ID", "disabled" => true])
            ->add('hiddenKey', TextType::class, ['label' => "Hidden column key", "required" => false])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Schedule is enabled',
                'required' => false
            ])
            ->add('channel', ChoiceType::class, [
                'label' => 'Channel',
                'expanded' => false,
                'choices' => $channels,
                'attr' => [
                    'class' => 'channel-selector'
                ]
            ])
            ->add('titleTemplate', TextType::class, [
                'label' => 'Title template', 
                'required' => false,
                'attr' => [
                    'class' => 'store-autocomplete',
                    'data-basepath' => self::CURRENT_DATA_SOURCE_PATH,
                    'data-namespace' => self::SOURCE_NAMESPACE,
                    'data-channel-ref' => 'select.channel-selector',
                    'data-simulate-context-callback' => 'Horaro.getSimulateContext'
                ]
            ])
            ->add('gameTemplate', TextType::class, [
                'label' => 'Game name template', 
                'required' => false,
                'attr' => [
                    'class' => 'store-autocomplete',
                    'data-basepath' => self::CURRENT_DATA_SOURCE_PATH,
                    'data-namespace' => self::SOURCE_NAMESPACE,
                    'data-channel-ref' => 'select.channel-selector',
                    'data-simulate-context-callback' => 'Horaro.getSimulateContext'
                ]
            ])
            ->add('announceTemplate', TextType::class, [
                'label' => 'Announce message template',
                'required' => false,
                'attr' => [
                    'class' => 'store-autocomplete',
                    'data-basepath' => self::NEXT_DATA_SOURCE_PATH,
                    'data-namespace' => self::SOURCE_NAMESPACE,
                    'data-channel-ref' => 'select.channel-selector',
                    'data-simulate-context-callback' => 'Horaro.getSimulateContext'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn-lg btn-primary waves-effect'
                ]
            ]);
    }
}
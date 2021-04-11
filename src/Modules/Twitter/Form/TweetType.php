<?php
namespace App\Modules\Twitter\Form;

use App\Modules\Twitter\Enum\StatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TweetType extends AbstractType
{
    const TRIGGER_TYPES = [
        'datetime' => 'form.trigger.datetime',
        'event' => 'form.trigger.event',
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('accounts');
        $resolver->setRequired('channels');
        $resolver->setDefaults([
            'translation_domain' => 'twitter'
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Combine accounts to get an assoc array with same keys/values
        $accounts = array_combine($options['accounts'], $options['accounts']);
        $channels = array_combine($options['channels'], $options['channels']);

        $builder
            ->add('id', HiddenType::class)
            ->add('account', ChoiceType::class, [
                'label' => 'form.account',
                'choices' => $accounts,
                'choice_translation_domain' => false
            ])
            ->add('content', TextareaType::class, [
                'label' => 'form.content', 
                'attr' => ['rows' => '4']
            ])
            ->add('media', FileType::class, [
                'label' => 'form.media',
                'multiple' => false,
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'form.status.label',
                'choices' => array_flip(StatusEnum::getLabels())
            ])
            ->add('trigger', ChoiceType::class, [
                'label' => 'form.trigger.type',
                'expanded' => true,
                'choices' => array_flip(self::TRIGGER_TYPES),
                'choice_translation_domain' => true
            ])
            ->add('sendTime', DateTimeType::class, [
                'label' => 'form.sending_time',
                'format' => 'yyyy-MM-dd HH:mm',
                'html5' => false,
                'widget' => 'single_text'
            ])
            ->add('event', TextType::class, [
                'label' => 'form.event'
            ])
            ->add('channel', ChoiceType::class, [
                'label' => 'form.bound_channel',
                'choices' => $channels,
                'choice_translation_domain' => false,
                'attr' => ['class' => 'channel-selector']
            ])
            ->add('constraints', CollectionType::class, [
                'label' => 'form.constraints',
                'entry_type' => ConstraintType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true
            ])
        ;
    }
}

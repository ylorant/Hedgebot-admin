<?php
namespace App\Modules\Twitter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\CallbackTransformer;

class TweetType extends AbstractType
{
    const TRIGGER_TYPES = [
        'datetime' => 'Set date/time',
        'event' => 'On a specific event'
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('accounts');
        $resolver->setRequired('channels');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Combine accounts to get an assoc array with same keys/values
        $accounts = array_combine($options['accounts'], $options['accounts']);
        $channels = array_combine($options['channels'], $options['channels']);

        $builder
            ->add('id', HiddenType::class)
            ->add('account', ChoiceType::class, ['label' => 'Account', 'choices' => $accounts])
            ->add('content', TextareaType::class, ['label' => 'Tweet content', 'attr' => ['rows' => '4']])
            ->add('media', FileType::class, ['label' => 'Media', 'multiple' => false, 'required' => false])
            ->add('sent', CheckboxType::class, ['label' => 'Sent', 'required' => false])
            ->add('sentTime', DateTimeType::class, [
                'label' => 'Sent time',
                'disabled' => true,
                'format' => 'yyyy-MM-dd HH:mm',
                'widget' => 'single_text'
            ])
            ->add('trigger', ChoiceType::class, [
                'label' => 'Trigger type',
                'expanded' => true,
                'choices' => array_flip(self::TRIGGER_TYPES)
            ])
            ->add('sendTime', DateTimeType::class, [
                'label' => 'Send date/time',
                'format' => 'yyyy-MM-dd HH:mm',
                'widget' => 'single_text'
            ])
            ->add('event', TextType::class, ['label' => 'Triggering event'])
            ->add('channel', ChoiceType::class, ['label' => 'Bound channel', 'choices' => $channels, 'attr' => ['class' => 'channel-selector']])
            ->add('constraints', CollectionType::class, [
                'label' => 'Constraints',
                'entry_type' => ConstraintType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true
            ])
        ;
    }
}

<?php
namespace App\Modules\Twitter\Form;

use App\Modules\Twitter\Enum\StatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TweetFilterType extends AbstractType
{
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
            ->add('account', ChoiceType::class, [
                'label' => 'form.account',
                'choices' => $accounts,
                'choice_translation_domain' => false,
                'placeholder' => "form.filter.all_accounts",
                'required' => false
            ])
            ->add('channel', ChoiceType::class, [
                'label' => 'form.bound_channel',
                'choices' => $channels,
                'choice_translation_domain' => false,
                'placeholder' => "form.filter.all_channels",
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'form.status.label',
                'choices' => array_flip(StatusEnum::getLabels()),
                'multiple' => true,
                'required' => false
            ]);
    }
}
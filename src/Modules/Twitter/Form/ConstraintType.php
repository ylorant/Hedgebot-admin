<?php
namespace App\Modules\Twitter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ConstraintType extends AbstractType
{
    const CONSTRAINT_TYPES = [
        'store' => 'form.constraint.store',
        'event' => 'form.constraint.event'
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        // Setting the data class as stdClass here, because otherwise the property resolver would try to access it as an
        // array and that causes errors
        // $resolver->setDefaults([
        //     'data_class' => 'stdClass'
        // ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, ['label' => false, 'choices' => array_flip(self::CONSTRAINT_TYPES), 'attr' => ['class' => 'constraint-type']])
            ->add('lval', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'form.constraint.lval',
                    'class' => 'constraint-lval store-autocomplete full-token',
                    'data-channel-ref' => 'select.channel-selector',
                    'data-context' => '.tweet-form'
                ]
            ])
            ->add('rval', TextType::class, [
                'label' => false, 
                'attr' => [
                    'placeholder' => 'form.constraint.rval'
                ]
            ])
        ;
    }
}

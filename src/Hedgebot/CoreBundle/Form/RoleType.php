<?php
namespace Hedgebot\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Hedgebot\CoreBundle\Form\Type\RightType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RoleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('rights');
        $resolver->setRequired('roles');
        $resolver->setDefault('allowIdEdit', false);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build parent roles choice list
        $rolesChoiceList = [];
        foreach($options['roles'] as $role)
            $rolesChoiceList[$role->name] = $role->id;

        $builder
            ->add('id', TextType::class, ['label' => 'Role ID', 'disabled' => !$options['allowIdEdit']])
            ->add('name', TextType::class, ['label' => 'Role name'])
            ->add('parent', ChoiceType::class, ['label' => 'Parent role', 'required' => false, 'placeholder' => '--- No parent ---', 'choices' => $rolesChoiceList])
            ->add('default', CheckboxType::class, ['value' => true, 'required' => false, 'label' => 'Default role', 'attr' => ['class' => 'filled-in']])
            ->add('rights', CollectionType::class, [
                'entry_type' => RightType::class,
                'label' => 'Rights',
                'entry_options' => [
                    'required' => false
                ]
            ])
            ->add('users', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'label' => 'Users',
                'entry_options' => [
                    'label' => false
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn-lg btn-primary waves-effect'
                ]
            ]);
        
        $builder->addModelTransformer(new RoleTransformer($options['rights'], $options['roles']));
    }
}
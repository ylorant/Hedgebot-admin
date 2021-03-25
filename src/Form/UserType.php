<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class UserType extends AbstractType implements DataTransformerInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('appRoles');
        $resolver->setRequired('isNew');
        $resolver->setDefaults([
            'allowAdminEdit' => false,
            'data_class' => User::class
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build parent roles choice list
        $appRolesChoiceList = [];
        foreach ($options['appRoles'] as $role) {
            $appRolesChoiceList[$role['name']] = $role['name'];
        }

        $idType = HiddenType::class;
        if ($options['allowAdminEdit'] && !$options['isNew']) {
            $idType = TextType::class;
        }

        $builder
            ->add('id', $idType, ['label' => 'form.id', 'disabled' => true])
            ->add('username', TextType::class, ['label' => 'form.username', 'disabled' => !$options['allowAdminEdit']])
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'choices' => $appRolesChoiceList,
                    'label' => false,
                    'required' => true,
                    'expanded' => true,
                    'multiple' => true,
                    'disabled' => !$options['allowAdminEdit']
                ]
            )
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'first_options'  => ['label' => 'form.new_password'],
                'second_options' => ['label' => 'form.password_confirmation']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.save',
                'attr' => [
                    'class' => 'btn-lg btn-primary waves-effect'
                ]
            ]);

        if (!$options['allowAdminEdit'] && !$options['isNew']) {
            $builder->add('password', PasswordType::class, [
                'label' => 'form.current_password',
                'required' => true,
                'mapped' => false,
                'constraints' => new SecurityAssert\UserPassword(),
                'help' => 'form.current_password_help'
            ]);
        }

        $builder->addModelTransformer($this);
    }

    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}

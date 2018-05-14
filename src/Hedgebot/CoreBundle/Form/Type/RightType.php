<?php
namespace Hedgebot\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Hedgebot\CoreBundle\Form\RightTransformer;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Hedgebot\CoreBundle\Form\RoleTransformer;

/** Right field type definition.
 * Handles the form display for a right, allowing the user to set wether it's an inherited right or not,
 * and wether it's granted or not.
 */
class RightType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'available_rights' => [],
            
            ]);
        
        $resolver->setAllowedTypes('available_rights', ['array', '\Traversable']);
    }
    
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("override", CheckboxType::class, ['value' => true, 'required' => false, 'attr' => ['class' => 'filled-in override-right']]);
        $builder->add("grant", SwitchType::class, ['value' => true, 'required' => false, 'attr' => ['class' => 'grant-right']]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label'] = RoleTransformer::denormalizeRight($form->getConfig()->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return "right";
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return FormType::class;
    }
}

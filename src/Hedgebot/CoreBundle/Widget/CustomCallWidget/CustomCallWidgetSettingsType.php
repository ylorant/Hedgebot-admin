<?php
namespace Hedgebot\CoreBundle\Widget\CustomCallWidget;

use Hedgebot\CoreBundle\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Hedgebot\CoreBundle\Entity\CustomCall;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomCallWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $repo = $options['entity_manager']->getRepository(CustomCall::class);
        $calls = $repo->findAll();

        $builder
            ->add('call', ChoiceType::class, [
                'choices' => $calls,
                'choice_value' => 'id',
                'choice_label' => function(CustomCall $call = null) {
                    return "#" . $call->getId() . ": " . $call->getMethod() . " " . $call->getUrl();
                }
            ])
            ->add('label', TextType::class, [
                'label' => 'Label'
            ]);
    }
}
<?php
namespace Hedgebot\Plugin\CustomCommandsBundle\Widget\SampleWidget;

use Hedgebot\CoreBundle\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SampleWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_field', TextType::class);
    }
}

<?php
namespace Hedgebot\CoreBundle\Widget\ChatWidget;

use Hedgebot\CoreBundle\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChatWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('channel', TextType::class);
    }
}
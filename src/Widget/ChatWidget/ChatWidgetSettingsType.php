<?php
namespace App\Widget\ChatWidget;

use App\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChatWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('channel', TextType::class, ['label' => "Channel"])
            ->add('light_chat', CheckboxType::class, ['label' => "Use lightweight chat (KapChat)", "required" => false]);
    }
}

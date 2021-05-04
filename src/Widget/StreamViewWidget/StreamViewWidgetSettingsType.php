<?php
namespace App\Widget\StreamViewWidget;

use App\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StreamViewWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('channel', TextType::class, ['label' => "Channel"]);
    }
}

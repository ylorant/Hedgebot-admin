<?php
namespace App\Modules\HedgebotCustomCommandsBundle\Widget\SampleWidget;

use App\Interfaces\WidgetSettingsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SampleWidgetSettingsType extends WidgetSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_field', TextType::class);
    }
}

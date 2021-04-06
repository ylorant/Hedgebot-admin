<?php
namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Switch form field type. Basically this is a checkbox type, but it
 * is set as different to allow the field to be built as the switch type.
 */
class SwitchType extends CheckboxType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "switch";
    }
}

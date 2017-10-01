<?php
namespace Hedgebot\CoreBundle\Interfaces;

use Symfony\Component\Form\AbstractType;

class WidgetSettingsType extends AbstractType
{
    public final function getBlockPrefix()
    {
        return null;
    }
}

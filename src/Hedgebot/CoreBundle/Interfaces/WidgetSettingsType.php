<?php
namespace Hedgebot\CoreBundle\Interfaces;

use Symfony\Component\Form\AbstractType;

class WidgetSettingsType extends AbstractType
{
    final public function getBlockPrefix()
    {
        return null;
    }
}

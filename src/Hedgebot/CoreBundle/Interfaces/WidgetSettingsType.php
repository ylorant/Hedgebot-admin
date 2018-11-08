<?php
namespace Hedgebot\CoreBundle\Interfaces;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WidgetSettingsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('entity_manager');
        $resolver->setRequired('hedgebot_api');
    }

    final public function getBlockPrefix()
    {
        return null;
    }
}

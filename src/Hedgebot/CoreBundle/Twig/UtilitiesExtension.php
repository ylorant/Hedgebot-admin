<?php
namespace Hedgebot\CoreBundle\Twig;

use Twig_Extension;

class UtilitiesExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('instanceof', [$this, 'isInstanceOf'])
        );
    }
    
    public function isInstanceOf($object, $class)
    {
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->isInstance($object);
    }
}
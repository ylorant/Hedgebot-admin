<?php
namespace Hedgebot\CoreBundle\Twig;

use Twig_Extension;
use Twig\TwigFilter;

class UtilitiesExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('instanceof', [$this, 'isInstanceOf'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('anonymize', [$this, 'anonymize'])
        ];
    }
    
    public function isInstanceOf($object, $class)
    {
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->isInstance($object);
    }

    public function anonymize($input)
    {
        $charactersToShow = strlen($input) > 16 ? 4 : ceil(strlen($input) / 4);
        return substr($input, 0, $charactersToShow). '***'. substr($input, -1 * $charactersToShow);
    }
}
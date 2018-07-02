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
            new TwigFilter('anonymize', [$this, 'anonymize']),
            new TwigFilter('to_array', [$this, 'toArray']),
            new TwigFilter('iconize', [$this, 'iconize'])
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

    public function toArray($input)
    {
        return (array) $input;
    }

    /**
     * Twig filter: Generates an icon from its key
     */
    public function iconize($input)
    {
        $iconParts = explode(':', $input, 2);
        
        // Default type: material_icons
        if(count($iconParts) == 1) {
            array_unshift($iconParts, 'material-icons');
        }
        
        $classes = $iconParts[0];
        $content = '';

        // Handling all types of icons that the admin is able to generate
        switch ($iconParts[0]) {
            case 'zmdi':
                $classes .= ' zmdi-' . $iconParts[1];
                break;
            
            case 'material-icons':
                $content = $iconParts[1];
                break;
        }

        return '<i class="' . $classes. '">' . $content. '</i>';
    }
}

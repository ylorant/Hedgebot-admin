<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

class UtilitiesExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceOf'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('anonymize', [$this, 'anonymize']),
            new TwigFilter('to_array', [$this, 'toArray']),
            new TwigFilter('iconize', [$this, 'iconize']),
            new TwigFilter('unique', [$this, 'unique'])
        ];
    }

    public function isInstanceOf($object, $class): bool
    {
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->isInstance($object);
    }

    public function anonymize($input): string
    {
        $charactersToShow = strlen($input) > 16 ? 4 : ceil(strlen($input) / 4);
        return substr($input, 0, $charactersToShow) . '***' . substr($input, -1 * $charactersToShow);
    }

    public function toArray($input): array
    {
        return (array) $input;
    }

    /**
     * Twig filter: Generates an icon from its key
     * @param $input
     * @return string
     */
    public function iconize($input): string
    {
        $iconParts = explode(':', $input, 2);
        // Default type: material_icons
        if (count($iconParts) == 1) {
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

        return '<i class="' . $classes . '">' . $content . '</i>';
    }

    /**
     * Filters all unique values from an array.
     *
     * @param array $input Input array
     * @return array Filtered array.
     */
    public function unique($input)
    {
        return array_unique($input);
    }
}

<?php
namespace Hedgebot\CoreBundle\Helper;

class ArrayHelper
{
    /**
     * Traverses a multidimensional array to fetch a specific value in it.
     *
     * @param $array        array  The array to traverse.
     * @param $selector     string The selector to get to the desired value. To separate dimensions, use points '.'.
     * @param $defaultValue mixed  The default value to return if the path does not resolve to an existing value.
     */
    public static function traverse(array $array, $selector, $defaultValue = null)
    {
        $selectorParts = explode('.', $selector);
        $value = $array;
        
        foreach ($selectorParts as $part) {
            if (!is_array($value) || !isset($value[$part])) {
                return $defaultValue;
            }
            
            $value = $value[$part];
        }
        
        return $value;
    }
}

<?php
namespace Hedgebot\CoreBundle\Form;

use Symfony\Component\Form\DataTransformerInterface;
use stdClass;

/** Role data transformer.
 * Transforms the data from the role representation given by the API (the norm model)
 * to the data that will be useful for the form to be rendered correctly.
 */
class RoleTransformer implements DataTransformerInterface
{
    protected $availableRights;
    protected $roles;

    /**
     * Constructor.
     *
     * @param array $availableRights A simple array containing the available rights that have to be listed
     *                               in the right list field.
     */
    public function __construct(array $availableRights, array $roles)
    {
        $this->availableRights = $availableRights;
        $this->roles = $roles;
    }

    /**
     * Transforms the norm data given to the form into the format that will be correct
     * for its fields. Here more specifically, it will transform the right list into the expanded
     * list of all the available rights and set the values for the fields depending on what has been
     * set on the given data.
     *
     * @param object $data The data to transform into its form view.
     * @return object The transformed data.
     */
    public function transform($data)
    {
        $rights = [];
        $setRights = $data->rights ?? [];
        $inheritedRights = $data->inheritedRights ?? [];

        // Cycle through the available rights to create each row for the rights list
        foreach ($this->availableRights as $right) {
            $normalizedRight = self::normalizeRight($right);
            $rights[$normalizedRight] = [
                'override' => isset($setRights[$right]),
                'grant' => !empty($setRights[$right]) || !empty($inheritedRights[$right]) ? true : false
            ];
        }
        
        // Update the data
        if (is_object($data)) {
            $data->rights = $rights;
        } else {
            $data = ['rights' => $rights];
        }

        return $data;
    }

    /**
     * Transforms back the data coming from the form view into the norm data that the role object should be receiving.
     *
     * @param object $data The form data to transform back into it's norm view.
     * @return object The normed data.
     */
    public function reverseTransform($data)
    {
        $roleRights = [];
        $data = (object) $data;

        // Cycle through all the rights in the data to keep only the overriden ones
        foreach ($data->rights as $rightName => $right) {
            $rightName = self::denormalizeRight($rightName);
            if (!empty($right['override'])) {
                $roleRights[$rightName] = $right['grant'];
            }
        }

        $data->rights = $roleRights;

        return $data;
    }

    /**
     * Normalizes a right name.
     * @param string $right The right name to normalize.
     * @return string       The normalized right name.
     */
    public static function normalizeRight($right)
    {
        return str_replace('/', '-', $right);
    }

    public static function denormalizeRight($right)
    {
        return str_replace('-', '/', $right);
    }
}

<?php
namespace Hedgebot\CoreBundle\API;

class NamedArgs
{
    private $args;

    public function __construct(array $args = array())
    {
        $this->args = $args;
    }

    public function __get($name)
    {
        if (isset($this->args[$args])) {
            return $this->args[$args];
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function toArray()
    {
        return $this->args;
    }
}

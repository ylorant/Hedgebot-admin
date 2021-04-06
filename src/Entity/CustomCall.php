<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="customcall")
 */
class CustomCall
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $method;
    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    protected $url;
    /**
     * @ORM\Column(type="object", nullable=true)
     */
    protected $parameters;

    public const METHOD_GET = "GET";
    public const METHOD_POST = "POST";
    public const METHOD_PUT = "PUT";
    public const METHOD_DELETE = "DELETE";

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parameters = new stdClass();
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @return  self
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get the value of url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @return  self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the value of parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set the value of parameters
     *
     * @return  self
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
}

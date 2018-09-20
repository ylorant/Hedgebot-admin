<?php
namespace Hedgebot\CoreBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    protected $username;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $password;
    
    /**
     * @ORM\Column(type="array", length=255)
     */
    protected $roles;
    
    /**
     * @ORM\Column(type="object", nullable=true)
     */
    protected $settings;
    
    public function __construct()
    {
        $this->settings = new stdClass();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    public function getSettings()
    {
        return $this->settings;
    }
    
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }
    
    public function getRoles()
    {
        return $this->roles;
    }
    
    
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }
    
    public function getSalt()
    {
        return null; // Using bcrypt, so salt isn't needed
    }
    
    public function eraseCredentials()
    {
    }
    
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
        ]);
    }
    
    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt
        ) = unserialize($serialized);
    }
}

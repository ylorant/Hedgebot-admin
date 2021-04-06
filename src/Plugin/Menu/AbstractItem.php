<?php
namespace App\Plugin\Menu;

/**
 * Abstract menu item.
 */
abstract class AbstractItem
{
    /** @var string title text */
    protected $title;

    /** @var MenuItemList Parent menu item list */
    protected $parent;

    public function __construct($title, $parent)
    {
        $this->title = $title;
        $this->parent = $parent;
    }

    /**
     * Gets the menu title.
     * 
     * @return string The menu title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the menu title.
     * 
     * @param string $title The menu title.
     * @return void 
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Ends the definition of the menu, i.e. returns the parent menu.
     * This is used mainly as a glue to allow chaining menu item definition.
     * 
     * @return MenuItemList The parent menu.
     */
    public function end()
    {
        return $this->parent;
    }
}

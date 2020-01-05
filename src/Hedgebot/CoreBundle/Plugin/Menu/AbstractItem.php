<?php
namespace Hedgebot\CoreBundle\Plugin\Menu;

/**
 * Abstract menu item.
 */
abstract class AbstractItem
{
    /** @var Menu title text */
    protected $title;

    /** @var MenuItemList Parent menu item list */
    protected $parent;

    public function __construct($title, $parent)
    {
        $this->title = $title;
        $this->parent = $parent;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function end()
    {
        return $this->parent;
    }
}
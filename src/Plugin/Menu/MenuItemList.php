<?php
namespace App\Plugin\Menu;

use Iterator;

/**
 * Menu item list container. Contains MenuItem instances.
 */
class MenuItemList implements Iterator
{
    /** @var array Sub-menu items */
    protected $items = [];

    /** @var MenuItem Parent menu */
    protected $parent;

    public function __construct(MenuItem $parent = null)
    {
        $this->parent = $parent;
    }

    public function header($title)
    {
        $item = new HeaderItem($title, $this);
        $this->add($item);

        return $item;
    }

    public function count()
    {
        return count($this->items);
    }

    public function item($title, $href = null, $icon = null)
    {
        $item = new MenuItem($title, $href, $icon, $this);
        $this->add($item);

        return $item;
    }

    public function add(AbstractItem $item)
    {
        $this->items[] = $item;
        return $item;
    }

    public function remove($title)
    {
        $foundIndex = null;

        foreach ($this->items as $index => $item) {
            if ($item->getTitle() === $title) {
                $foundIndex = $index;
                break;
            }
        }

        if (!empty($foundIndex)) {
            unset($this->items[$foundIndex]);
        }

        return $this;
    }

    public function end()
    {
        return $this->parent;
    }

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function rewind()
    {
        return reset($this->items);
    }

    public function valid()
    {
        return $this->current() !== false;
    }
}

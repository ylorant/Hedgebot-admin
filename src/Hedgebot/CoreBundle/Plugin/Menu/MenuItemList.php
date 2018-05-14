<?php
namespace Hedgebot\CoreBundle\Plugin\Menu;

use Iterator;

/**
 * Menu item list container. Contains MenuItem instances.
 */
class MenuItemList implements Iterator
{
    /** @var Sub-menu items */
    protected $items = [];
    
    /** @var Parent menu */
    protected $parent;
    
    public function __construct(MenuItem $parent = null)
    {
        $this->parent = $parent;
    }
    
    public function item($title, $href = null, $icon = null)
    {
        $item = new MenuItem($title, $href, $icon, $this);
        $this->add($item);
        
        return $item;
    }
    
    public function add(MenuItem $item)
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

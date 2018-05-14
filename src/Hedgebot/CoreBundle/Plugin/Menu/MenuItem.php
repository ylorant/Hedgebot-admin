<?php
namespace Hedgebot\CoreBundle\Plugin\Menu;

class MenuItem
{
    /** @var Menu title text */
    protected $title;
    
    /** @var Target url for the node */
    protected $route;
    
    /** @var Icon for the node */
    protected $icon;
    
    /** @var MenuItemList Submenu item list */
    protected $submenu;
    
    /** @var MenuItemList Parent menu item list */
    protected $parent;
    
    public function __construct($title, $route = null, $icon = null, $parent = null)
    {
        $this->title = $title;
        $this->route = $route;
        $this->icon = $icon;
        $this->parent = $parent;
    }
    
    public function children()
    {
        $this->submenu = new MenuItemList($this);
        return $this->submenu;
    }
    
    public function end()
    {
        return $this->parent;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function getIcon()
    {
        return $this->icon;
    }
    
    public function getSubmenu()
    {
        return $this->submenu;
    }
}

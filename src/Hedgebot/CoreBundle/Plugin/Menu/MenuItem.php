<?php
namespace Hedgebot\CoreBundle\Plugin\Menu;

class MenuItem extends AbstractItem
{
    /** @var Target url for the node */
    protected $route;
    
    /** @var Icon for the node */
    protected $icon;
    
    /** @var MenuItemList Submenu item list */
    protected $submenu;
    
    public function __construct($title, $route = null, $icon = null, $parent = null)
    {
        parent::__construct($title, $parent);

        $this->route = $route;
        $this->icon = $icon;
    }
    
    public function children()
    {
        $this->submenu = new MenuItemList($this);
        return $this->submenu;
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

<?php
namespace Hedgebot\CoreBundle\Entity;

use Hedgebot\CoreBundle\Service\DashboardWidgetsManagerService;

class DashboardLayout
{
    /** @var DashboardWidgetsManagerService The dashboard widgets manager service. */
    protected $widgetsManager;
    /** @var string Layout type */
    protected $type;
    /** @var array List of widgets in the layout */
    protected $widgets;

    public function __construct(DashboardWidgetsManagerService $widgetsManager = null)
    {
        $this->widgetsManager = $widgetsManager;
        $this->widgets = [];
    }

    /**
     * Gets the layout type ID.
     * @return string The layout type ID.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the layout type.
     * @param string $type The layout type ID.
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Adds a widget to the layout.
     * @param  string   $block          The block the widget belongs to.
     * @param  string   $widgetType     The widget type.
     * @param  string   $id             The widget's ID. If omitted, a random one will be generated.
     * @param  int      $position       The position of the widget in its block. If omitted, it will be put at the end.
     * @param  array    $widgetSettings The widget particular settings. Defaults to an empty array.
     * @return string                   The new widget's UUID.
     */
    public function addWidget($block, $widgetType, $id = null, $position = null, array $widgetSettings = [])
    {
        $widget = (object) [
            'id' => $id ? $id : $this->widgetsManager->generateWidgetID(),
            'type' => $widgetType,
            'block' => $block,
            'position' => !empty($position) ? $position : count($this->getBlockWidgets($block)),
            'settings' => $widgetSettings
        ];

        $this->widgets[] = $widget;
        return $widget->id;
    }

    /**
     * Removes a widget from the layout.
     * @param  string $widgetId The ID of the widget to remove.
     * @return bool             True if the widget has been successfully removed, false if the widget hasn't been found.
     */
    public function removeWidget($widgetId)
    {
        foreach ($this->widgets as $index => $widget) {
            if ($widget->id == $widgetId) {
                unset($this->widgets[$index]);
                return true;
            }
        }

        return false;
    }

    /**
     * Removes all widgets in the layout.
     */
    public function clearWidgets()
    {
        $this->widgets = [];
    }

    /**
     * Finds a widget by its ID.
     * @param  string      $widgetId The id of the widget to find.
     * @return object|null           The widget data if found or null if not found.
     */
    public function getWidgetById($widgetId)
    {
        foreach ($this->widgets as $widget) {
            if ($widget->id == $widgetId) {
                return $widget;
            }
        }

        return null;
    }

    /**
     * Gets all the widgets of the layout.
     * @return array The list of all the widgets in the layout.
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Gets the list of widgets that belong to a block.
     * @param  string $blockId The ID of the block to get widgets for.
     * @return array           An array containing the widgets for the given block.
     */
    public function getBlockWidgets($blockId)
    {
        $widgets = [];

        foreach ($this->widgets as $widget) {
            if ($widget->block == $blockId) {
                $widgets[] = $widget;
            }
        }

        return $widgets;
    }

    /**
     * Formats the layout as an array for easy storage.
     * @return array An array representation of the layout.
     */
    public function toArray()
    {
        return [
            'type' => $this->type,
            'widgets' => $this->widgets
        ];
    }

    /**
     * Restores a dashboard layout from its array representation.
     * @param  array $data The array data for the layout.
     * @return bool        True if the restoration has been completed successfully, false if not.
     */
    public function fromArray($data)
    {
        if (empty($data['type']) || empty($data['widgets'])) {
            return false;
        }

        $this->type = $data['type'];
        $this->widgets = $data['widgets'];

        return true;
    }
}

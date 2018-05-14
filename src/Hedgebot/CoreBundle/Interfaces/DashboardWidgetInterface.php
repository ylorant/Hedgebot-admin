<?php
namespace Hedgebot\CoreBundle\Interfaces;

/**
 * Dashboard Widget interface. Defines how a widget should be giving out its info.
 *
 * FIXME: Put all methods as static, and give the class name to the widget manager service ?
 */
interface DashboardWidgetInterface
{
    /**
     * Gets the widget identifier. This identifier must be lowercase, can have numbers, dashes and underscores.
     *
     * @return string The ID for the widget.
     */
    public function getId();

    /**
     * Returns the legible name for the widget.
     *
     * @return string The legible name for the widget.
     */
    public function getName();

    /**
     * Returns the description of the widget.
     *
     * @return string The widget description.
     */
    public function getDescription();

    /**
     * Gets the view name (i.e. the template name) for the widget. This name will be resolved.
     *
     * @return string The view name for the widget.
     */
    public function getViewName();

    /**
     * Gets the settings form type class for the widget.
     *
     * @return string The form type class for the widget's settings.
     */
    public function getSettingsFormType();

    /**
     * Updates the data for the widget.
     *
     * @return array This method should return the data for the widget, that will then be accessible in the template.
     */
    public function update(array $settings = []);
}

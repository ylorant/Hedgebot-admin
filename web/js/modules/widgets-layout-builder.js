var WidgetsLayoutBuilder = {

    defaultOptions: {
        /**
         * Layout list.
         * @type {Array}
         */
        layouts: [],

        /**
         * Layout workspace element path/query.
         * @type {String}
         */
        layoutWorkspace: "",

        /**
         * Layout style selector element path/query.
         * @type {String}
         */
        layoutSelector: "",

        /**
         * Widget list element path/query.
         * @type {String}
         */
        widgetList: "",

        /**
         * The Symfony route that the module will call to populate a widget with its settings form.
         * @type {String}
         */
        parametersFormRoute: "",

        /**
         * The Symfony route which will be called to save the layout.
         * @type {String}
         */
        saveRoute: "",

        /**
         * Selector for the form englobing all the settings view.
         * A trigger on its submission will be used to handle saving.
         * @type {String}
         */
        form: ""
    },

    selectedLayout: null,
    options: null,
    addTargetBlock: null,
    addTargetElement: null,
    workspaceElement: null,
    widgetListElement: null,
    layoutSelectorElement: null,
    formElement: null,

    /**
     * Module initialization.
     * @param {object} options List of options to initialize this module. Refer to defaultOptions to see what options are available.
     */
    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();

        $(this.options.layoutSelector).trigger('change');
    },

    /**
     * Initializes elements
     */
    initElements: function()
    {
        // Resolving elements right now because they're not supposed to change a lot.
        this.layoutSelectorElement = $(this.options.layoutSelector);
        this.workspaceElement = $(this.options.layoutWorkspace);
        this.widgetListElement = $(this.options.widgetList);
        this.formElement = $(this.options.form);

        // Remove all options in the selector
        this.layoutSelectorElement.children('option').remove();

        for(var i in this.options.layouts)
        {
            var currentLayout = this.options.layouts[i];
            var option = document.createElement('option');
            option.setAttribute('value', i);
            option.innerText = currentLayout.name;

            this.layoutSelectorElement.append(option);
        }

        // Refresh the select if it has bootstrap selectpicker
        if(this.layoutSelectorElement.parent('.bootstrap-select').length > 0)
            this.layoutSelectorElement.selectpicker('refresh');

    },

    /**
     * Binds events to UI elements.
     */
    bindUIActions: function()
    {
        // Unbind previously bound events
        this.layoutSelectorElement.off('change');
        this.widgetListElement.off('click');
        this.widgetListElement.off('mouseover');
        this.widgetListElement.off('mouseout');
        this.workspaceElement.off('click');
        this.formElement.off('submit');

        // Bind events
        this.layoutSelectorElement.on('change', this.onLayoutSelectChange.bind(this)); // Layout change event
        this.widgetListElement.on('click', '.widget', this.onWidgetListClick.bind(this)); // Click on a widget from the widget list modal
        this.widgetListElement.on('mouseover', '.widget', this.onWidgetHover.bind(this));
        this.widgetListElement.on('mouseout', '.widget', this.onWidgetOut.bind(this));
        this.workspaceElement.on('click', '.widget-block-add', this.onWidgetAddModalOpen.bind(this)); // Clicking on an "add widget" button
        this.workspaceElement.on('click', '.widget-actions .delete', this.onWidgetDelete.bind(this));
        this.workspaceElement.on('click', '.widget-actions .move-up', this.onWidgetMoveUp.bind(this));
        this.workspaceElement.on('click', '.widget-actions .move-down', this.onWidgetMoveDown.bind(this));
        this.formElement.on('submit', this.onFormSubmit.bind(this));
    },

    //// ACTION METHODS ////

    /**
     * Fills the layout with the given widgets.
     * @param  {array} layoutWidgets An array containing all the widgets of the layout.
     * @return {bool}                True if the filling worked, false otherwise.
     */
    fillLayout: function(layoutWidgets)
    {
        var currentLayout = this.options.layouts[this.selectedLayout];

        // Prepare the method that will fill the widgets with their settings upon loading
        var fillSettingsCallback = function(settings, widget)
        {
            for(var settingName in settings) {
                var input = $('[name="' + settingName + '"]', widget);

                switch(input.attr('type')) {
                    case "checkbox":
                        if(settings[settingName] !== null) {    
                            input.attr("checked", true);
                        }
                        break;
                    default:
                        input.trigger('focus').val(settings[settingName]).trigger('blur');
                }
            }
        };

        // Iterate over the current layout's widgets and check that the block exists for each widget
        for(var i = 0; i < layoutWidgets.length; i++)
        {
            var widget = layoutWidgets[i];

            // Load the widget and set up the callback to use for filling their settings
            if(currentLayout.blocks[widget.block])
                this.addWidget(widget.type, widget.block, fillSettingsCallback.bind(this, widget.settings), widget.id);
        }

        return true;
    },

    /**
     * Updates the widget blocks layout with the given one. The layout has to exist within the layouts
     * given at the module instanciation, or else this function will fail.
     *
     * @param {string} selectedLayout The layout to change to. Has to be an existing layout.
     * @return {bool} True if the layout has been successfully refeshed, False otherwise.
     */
    changeLayoutType: function(selectedLayout)
    {
        this.selectedLayout = selectedLayout;

        // Clean the layout elements
        this.workspaceElement.children('.widget-block-container').remove();

        if(typeof this.options.layouts[this.selectedLayout] == "undefined")
            return false;

        for(var blockId in this.options.layouts[this.selectedLayout].blocks)
        {
            var block = this.options.layouts[this.selectedLayout].blocks[blockId];

            var blockSpaceElement = document.createElement('div');
            blockSpaceElement.dataset.id = blockId;
            blockSpaceElement.classList.add('widget-block-container');
            blockSpaceElement.classList.add('col-lg-' + block.width.toString(), 'col-xs-12');

            var blockElement = document.createElement('div');
            blockElement.classList.add('widget-block');
            blockElement.classList.add(...(block.class.split(' ')));
            blockElement.dataset.id = blockId;

            var blockTitle = document.createElement('span');
            blockTitle.classList.add('widget-block-title');
            blockTitle.innerText = block.title;

            var blockAddWidget = document.createElement('span');
            blockAddWidget.classList.add(...('widget-block-add btn bg-blue btn-circle-lg waves-effect'.split(' ')));
            blockAddWidget.dataset.toggle = "modal";
            blockAddWidget.title = "Add widget...";
            blockAddWidget.dataset.target = this.options.widgetList;
            blockAddWidget.innerText = "+";

            blockElement.appendChild(blockTitle);
            blockElement.appendChild(blockAddWidget);
            blockSpaceElement.appendChild(blockElement);
            this.workspaceElement.append(blockSpaceElement);
        }

        // If needed, update the select to reflect the change
        if(this.layoutSelectorElement.val() != selectedLayout)
            this.layoutSelectorElement.val(selectedLayout).trigger('change');

        return true;
    },

    /**
     * Adds a widget to a certain block.
     * @param  {HTMLElement|JQuery|string} widget      The ID of the widget to add.
     * @param  {HTMLElement|JQuery|string} targetBlock The target block ID to add the widget to.
     * @return {HTMLElement|bool}                      The new widget if it has been added, False if there was an error.
     */
    addWidget: function(widget, targetBlock, callback, id)
    {
        // Resolve the widget
        if(typeof widget == "string")
            widget = $('.widget[data-type="' + widget + '"]', this.widgetListElement);
        else if(widget instanceof HTMLElement)
            widget = $(widget);

        // Now, check that the widget is valid
        if(!(widget instanceof $) || !widget.length || !widget.hasClass('widget'))
            return false;

        // Resolve the target block
        if(typeof targetBlock == "string")
            targetBlock = $('.widget-block[data-id="' + targetBlock + '"]', this.workspaceElement);
        else if(widget instanceof HTMLElement)
            targetBlock = $(targetBlock);

        // Check that the target is a valid target
        if(!(targetBlock instanceof $) || !targetBlock.length || !targetBlock.hasClass('widget-block'))
            return false;

        var widgetClone = widget.clone();

        // Setup the widget
        widgetClone.find('.waves-ripple').remove(); // Cancel every ripple effect that could be happening
        widgetClone.removeClass('waves-effect');
        widgetClone.find('.body').text('');

        // Set the widget ID if needed
        if(id)
            widgetClone.data('id', id);

        // Add a loader and ask for the parameters form to the server
        var loader = $('.preloader.hidden').clone();
        loader.removeClass('hidden');
        widgetClone.find('.body').append(loader);

        // Call the form builder for this widget
        $.ajax({
           url: Routing.generate(this.options.parametersFormRoute, { widgetName: widget.data('type') }, true),
           type: 'get',
           success: this.onGetWidgetParamForm.bind(this, widgetClone, callback)
        });

        // Action box
        var actionBox = document.createElement('div');
        actionBox.classList.add('widget-actions', 'pull-left');

        // Add the move and delete icons
        var moveUpIcon = document.createElement('span');
        moveUpIcon.classList.add('material-icons', 'move-up', 'waves-effect');
        moveUpIcon.innerText = 'expand_less';
        actionBox.appendChild(moveUpIcon);

        var moveDownIcon = document.createElement('span');
        moveDownIcon.classList.add('material-icons', 'move-down', 'waves-effect');
        moveDownIcon.innerText = 'expand_more';
        actionBox.appendChild(moveDownIcon);

        var deleteIcon = document.createElement('span');
        deleteIcon.classList.add('material-icons', 'delete', 'waves-effect');
        deleteIcon.innerText = 'delete';
        actionBox.appendChild(deleteIcon);

        widgetClone.find('.header').prepend(actionBox);

        // Add the widget in the block
        targetBlock.find('.widget-block-add').before(widgetClone);

        return widgetClone.get(0);
    },

    /**
     * Moves a widget up in its block.
     * @param  {HTMLElement|JQuery} widget The widget to move up.
     * @return {bool} True if the widget has been moved, False if not.
     */
    moveWidgetUp: function(widget)
    {
        // Convert widget to jquery if needed
        if(!widget.jquery)
            widget = $(widget);

        var prevWidget = widget.prev();

        // Only move up if the element is a widget and the previous one is too
        if(!prevWidget.is('.widget') || !widget.is('.widget'))
            return false;

        prevWidget.before(widget);
        return true;
    },

    /**
     * Moves a widget down in its block.
     * @param  {HTMLElement|JQuery} widget The widget to move down.
     * @return {bool} True if the widget has been moved, False if not.
     */
    moveWidgetDown: function(widget)
    {
        // Convert widget to jquery if needed
        if(!widget.jquery)
            widget = $(widget);

        var nextWidget = widget.next();

        // Only move the widget if the next one is effectively a widget, and if the current element is a widget too
        if(!nextWidget.is('.widget') || !widget.is('.widget'))
            return false;

        nextWidget.after(widget);
        return true;
    },

    /**
     * Removes a widget from the layout.
     *
     * @param  {HTMLElement|JQuery} widget The widget to remove.
     * @return {bool} True if the widget has been removed, False if not.
     */
    removeWidget: function(widget)
    {
        // Convert widget to jquery if needed
        if(!widget.jquery)
            widget = $(widget);

        if(!widget.is('.widget'))
            return false;

        widget.remove();
        return true;
    },

    /**
     * Saves the current layout be sending it to the server in an AJAX query.
     * @return {bool} True if the layout has been saved, false otherwise.
     */
    saveLayout: function(callback)
    {
        // Preparing data object
        var saveData = {
            layout: "",
            widgets: []
        };

        // Setting selected layout, this one's pretty easy
        saveData.layout = this.layoutSelectorElement.val();

        // Discovering blocks from the layout schema
        for(var blockID in this.options.layouts[saveData.layout].blocks)
        {
            // Discovering widgets and iterating through them to save them
            var blockWidgets = $('.widget-block[data-id="' + blockID + '"]', this.workspaceElement).find('.widget');
            for(var i = 0; i < blockWidgets.length; i++)
            {
                var widget = blockWidgets.eq(i);
                widgetObj = {
                    type: widget.data('type'),
                    id: widget.data('id'),
                    block: blockID,
                    position: i,
                    settings: {}
                };

                // Saving settings
                var settingsInputs = $(':input:not([type=hidden])', widget);
                for(var j = 0; j < settingsInputs.length; j++)
                {
                    var input = settingsInputs.get(j);

                    switch(input.getAttribute("type")) {
                        case "checkbox":
                            if($(input).is(":checked")) {
                                widgetObj.settings[input.name] = input.value;
                            }
                            break;
                        default:
                            widgetObj.settings[input.name] = input.value;
                        }
                }

                // Save the widget into the block
                saveData.widgets.push(widgetObj);
            }
        }

        // Send the layout to the server
        $.ajax({
            url: Routing.generate(this.options.saveRoute, {}, true),
            type: 'post',
            data: saveData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                var data = jqXHR.responseJSON;
                callback(textStatus == "success" && data === true);
            }
        });

        return true;
    },

    //// EVENTS ////

    /**
     * Event: A new layout has been selected, we update the layout zone to reflect the selected layout
     */
    onLayoutSelectChange: function()
    {
        // Refresh the layout
        var changed = this.changeLayoutType(this.layoutSelectorElement.val());

        if(!changed)
            $.notify({message: 'An error occured when changing layout.'}, {type: 'danger'});
    },

    /**
     * Event: The modal to add a widget is opened.
     */
    onWidgetAddModalOpen: function(ev)
    {
        var blockElement = $(ev.currentTarget).parents('.widget-block');
        this.addTargetBlock = blockElement.data('id');
    },

    /**
     * Event: A widget from the widget list has been clicked, we add it on the layout
     */
    onWidgetListClick: function(ev)
    {
        var widgetBlock = $(ev.target);

        if(!widgetBlock.is('.widget'))
            widgetBlock = widgetBlock.parents('.widget');

        // Force the hover effect off from this widget
        this.onWidgetOut(ev);

        var widgetType = widgetBlock.data('type');
        this.addWidget(widgetType, this.addTargetBlock);
        this.addTargetBlock = null;

        ev.preventDefault();
        return false;
    },

    /**
     * AJAX Callback: Got the form for the widget from the server.
     */
    onGetWidgetParamForm: function(widget, callback, data)
    {
        // Replace the preloader by the retrieved html
        widget.find('.body').html(data);

        if(callback)
            callback(widget);
    },

    /**
     * Event: Move a widget up in its block.
     */
    onWidgetMoveUp: function(ev)
    {
        var widget = $(ev.target).parents('.widget');

        this.moveWidgetUp(widget);

        ev.preventDefault();
        return false;
    },

    /**
     * Event: Move a widget down in its block.
     */
    onWidgetMoveDown: function(ev)
    {
        var widget = $(ev.target).parents('.widget');

        this.moveWidgetDown(widget);

        ev.preventDefault();
        return false;
    },

    /**
     * Event: Delete a widget.
     */
    onWidgetDelete: function(ev)
    {
        var widget = $(ev.target).parents('.widget');

        this.removeWidget(widget);

        ev.preventDefault();
        return false;
    },

    /**
     * Event: Mouse is hovering a widget. Adding hover classes.
     */
    onWidgetHover: function(ev)
    {
        var widgetBlock = $(ev.target);

        if(!widgetBlock.is('.widget'))
            widgetBlock = widgetBlock.parents('.widget');

        if(widgetBlock.hasClass('bg-green'))
            return;

        widgetBlock.addClass('bg-green');
        widgetBlock.find('.body').addClass('bg-green');
    },

    /**
     * Event: Mouse is exiting a widget. Removing the hover classes.
     */
    onWidgetOut: function(ev)
    {
        var widgetBlock = $(ev.target);

        if(!widgetBlock.is('.widget'))
            widgetBlock = widgetBlock.parents('.widget');

        if(!widgetBlock.hasClass('bg-green'))
            return;

        widgetBlock.removeClass('bg-green');
        widgetBlock.find('.body').removeClass('bg-green');
    },

    /**
     * Event: the form has been submitted, we save the layout.
     */
    onFormSubmit: function(ev)
    {
        this.saveLayout(this.onSaveResult.bind(this));

        ev.preventDefault();
        return false;
    },

    onSaveResult: function(success)
    {
        if(success)
            $.notify({ message: "Layout has been saved." });
        else
            $.notify({ message: "An error occured during saving." }, { type: "danger" });

    }
};

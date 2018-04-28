var CustomCommands = {

    defaultOptions: {
        /**
         * @var string Selector pointing to the command list container
         */
        commandContainerSelector: null,

        /**
         * @var string Selector for one command, inside the command container
         */
        commandSelector: null,

        /**
         * @var string The selector for the "add" button
         */
        addSelector: null,

        /**
         * @var string Route to delete a command on the controller
         */
        deleteRoute: null,

        /**
         * @var string Route to save a command on the controller
         */
        saveRoute: null,

        /**
         * @var object Field names for each value
         */
        fieldNames: {
            name: null,
            text: null,
            channels: null
        }
    },

    options: {},
    elements: {
        commandContainer: null,
        addButton: null,
    },

    /**
     * Initializes the module
     * 
     * @param array options The option list
     */
    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();
    },

    /**
     * Initializes the elements.
     */
    initElements: function(refresh)
    {
        this.elements.commandContainer = $(this.options.commandContainerSelector);
        this.elements.addButton = $(this.options.addSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        this.elements.commandContainer.on('click', '[data-action="save"]', this.onSaveCommand.bind(this));
        this.elements.commandContainer.on('click', '[data-action="delete"]', this.onDeleteCommand.bind(this));
    },

    /// EVENTS ///

    /**
     * Event: A delete command button has been pressed
     */
    onDeleteCommand: function(ev)
    {
        var commandBlock = this.elements.commandContainer.find(ev.currentTarget.dataset.target);
        this.deleteCommand(commandBlock.data('name'), this.onCommandDeletedResult.bind(this, commandBlock));
    },
    
    /**
     * Event: A command has been deleted.
     */
    onCommandDeletedResult: function(success)
    {
        // Deletion has been successful, we remove the command block
        if(success) {
            commandBlock.remove();
            $.notify({ message: "Command has been deleted." });
        }
        else
            $.notify({ message: "An error occured during command deletion." }, { type: "danger" });
    },

    /**
     * Event: Saves a command
     */
    onSaveCommand: function(ev)
    {
        var commandBlock = this.elements.commandContainer.find(ev.currentTarget.dataset.target);
        var commandData = {};

        commandData.name = commandBlock.find('[name="' + this.options.fieldNames.name + '"]').val();
        commandData.text = commandBlock.find('[name="' + this.options.fieldNames.text + '"]').val();
        commandData.channels = commandBlock.find('[name="' + this.options.fieldNames.channels + '"]').val();
        
        this.saveCommand(commandBlock.data('name'), commandData, this.onCommandSavedResult.bind(this));
    },

    /**
     * Event: A command has been saved.
     */
    onCommandSavedResult: function(ev, success)
    {
        if(success) {
            $.notify({ message: "Command has been updated." });
        }
        else
            $.notify({ message: "An error occured during command update." }, { type: "danger" });
    },

    /// ACTIONS ///

    saveCommand: function(commandName, commandData, callback)
    {
        $.ajax({
            url: Routing.generate(this.options.saveRoute, {name: commandName}, true),
            type: 'post',
            data: commandData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                var data = jqXHR.responseJSON;
                callback(textStatus == "success" && data === true);
            }
        });
    },

    deleteCommand: function(commandName, callback)
    {
        $.ajax({
            url: Routing.generate(this.options.deleteRoute, {name: commandName}, true),
            type: 'get',
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                var data = jqXHR.responseJSON;
                callback(textStatus == "success" && data === true);
            }
        });
    }
};
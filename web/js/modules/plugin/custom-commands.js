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
         * @var string Selector to the command template element
         */
        commandTemplateSelector: null,

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
        commandTemplate: null
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
        this.elements.commandTemplate = $(this.options.commandTemplateSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        this.elements.commandContainer.on('click', '[data-action="save"]', this.onSaveCommandClick.bind(this));
        this.elements.commandContainer.on('click', '[data-action="delete"]', this.onDeleteCommandClick.bind(this));

        this.elements.addButton.on('click', this.onAddCommandClick.bind(this));
    },

    /// EVENTS ///

    /**
     * Event: the Add command button has been pressed
     */
    onAddCommandClick: function()
    {
        this.addCommand();
    },

    /**
     * Event: A delete command button has been pressed
     */
    onDeleteCommandClick: function(ev)
    {
        var commandBlock = this.elements.commandContainer.find(ev.currentTarget.dataset.target).not();

        // If this is an already saved command, there should be a valid target
        if(commandBlock.length > 0)
            this.deleteCommand(commandBlock.data('name'), this.onCommandDeletedResult.bind(this, commandBlock));
        else // This is a new, not-yet-saved command, we just delete the matching parent command element
            $(ev.currentTarget).parents(this.options.commandSelector).remove();

    },
    
    /**
     * Event: A command has been deleted.
     */
    onCommandDeletedResult: function(commandBlock, success)
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
    onSaveCommandClick: function(ev)
    {
        var commandBlock = this.elements.commandContainer.find(ev.currentTarget.dataset.target);
        var commandData = {};
        var commandName = null;

        // If the command block has not been found, then it's a new command and just find the parent block that matches the command selector
        if(commandBlock.length == 0)
            commandBlock = $(ev.currentTarget).parents(this.options.commandSelector);

        commandName = commandBlock.data('name');

        commandData.name = commandBlock.find('[name="' + this.options.fieldNames.name + '"]').val();
        commandData.text = commandBlock.find('[name="' + this.options.fieldNames.text + '"]').val();
        commandData.channels = commandBlock.find('[name="' + this.options.fieldNames.channels + '"]').val();
        
        // If it's a new command, the commandName var will not be filled, so we'll fetch it from the actual input
        if(!commandName)
            commandName = commandData.name;

        this.saveCommand(commandName, commandData, this.onCommandSavedResult.bind(this, commandBlock, commandData));
    },

    /**
     * Event: A command has been saved.
     */
    onCommandSavedResult: function(commandBlock, commandData, success)
    {
        // If the save has succeeded, we need to update the block with the new ID and command name, and their references
        if(success) {
            commandBlock.data('name', commandData.name);
            commandBlock.attr('data-name', commandData.name);
            commandBlock.attr('id', 'command-' + commandData.name);

            commandBlock.find('[data-action="save"]').attr('data-target', "#command-" + commandData.name);
            commandBlock.find('[data-action="delete"]').attr('data-target', "#command-" + commandData.name);

            commandBlock.removeClass('not-saved');
            $.notify({ message: "Command has been saved." });
        }
        else
            $.notify({ message: "An error occured during command save." }, { type: "danger" });
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
    },

    addCommand: function()
    {
        var newCommandBlock = this.elements.commandTemplate.clone();
        newCommandBlock.addClass('not-saved');
        newCommandBlock.attr("id", "#command-" + this.makeID(10)); // Set the ID to a random one

        $('select', newCommandBlock).removeClass('ms').selectpicker();

        this.elements.commandContainer.append(newCommandBlock);
    },

    makeID: function(length)
    {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      
        for (var i = 0; i < length; i++)
          text += possible.charAt(Math.floor(Math.random() * possible.length));
      
        return text;
    }
};
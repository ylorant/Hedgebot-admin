var Announcements = {

    defaultOptions: {
        /**
         * @var string Selector pointing to the messages list container
         */
        messageContainerSelector: null,

        /**
         * @var string Selector for one message, inside the message container
         */
        messageSelector: null,

        /**
         * @var string Selector to the message template element
         */
        messageTemplateSelector: null,

        /**
         * @var string The selector for the "add message" button
         */
        addMessageSelector: null,

        /**
         * @var string Route to delete a message on the controller
         */
        deleteMessageRoute: null,

        /**
         * @var string Route to save a message on the controller
         */
        saveMessageRoute: null,

        /**
         * @var object Field names for each value
         */
        fieldNames: {
            message: null,
            channels: null
        }
    },

    options: {},
    elements: {
        messageContainer: null,
        addMessageButton: null,
        messageTemplate: null
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
        this.elements.messageContainer = $(this.options.messageContainerSelector);
        this.elements.addMessageButton = $(this.options.addMessageSelector);
        this.elements.messageTemplate = $(this.options.messageTemplateSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        this.elements.messageContainer.on('click', '[data-action="save"]', this.onSaveMessageClick.bind(this));
        this.elements.messageContainer.on('click', '[data-action="delete"]', this.onDeleteMessageClick.bind(this));

        this.elements.addMessageButton.on('click', this.onAddMessageClick.bind(this));
    },

    /// EVENTS ///

    /**
     * Event: the Add message button has been pressed
     */
    onAddMessageClick: function()
    {
        this.addMessage();
    },

    /**
     * Event: A delete message button has been pressed
     */
    onDeleteMessageClick: function(ev)
    {
        let messageBlock = this.elements.messageContainer.find(ev.currentTarget.dataset.target).not();

        // If this is an already saved message, there should be a valid target
        if(messageBlock.length > 0)
            this.deleteMessage(messageBlock.data('name'), this.onMessageDeletedResult.bind(this, messageBlock));
        else // This is a new, not-yet-saved message, we just delete the matching parent message element
            $(ev.currentTarget).parents(this.options.messageSelector).remove();

    },
    
    /**
     * Event: A message has been deleted.
     */
    onMessageDeletedResult: function(messageBlock, success)
    {
        // Deletion has been successful, we remove the message block
        if(success) {
            messageBlock.remove();
            $.notify({ message: "Message has been deleted." });
        }
        else
            $.notify({ message: "An error occured during message deletion." }, { type: "danger" });
    },

    /**
     * Event: Saves a message
     */
    onSaveMessageClick: function(ev)
    {
        let messageBlock = this.elements.messageContainer.find(ev.currentTarget.dataset.target);
        let messageData = {};

        // If the message block has not been found, then it's a new message and just find the parent block that matches the message selector
        if(messageBlock.length == 0)
            messageBlock = $(ev.currentTarget).parents(this.options.messageSelector);

        messageData.id = messageBlock.id;
        messageData.message = messageBlock.find('[name="' + this.options.fieldNames.text + '"]').val();
        messageData.channels = messageBlock.find('[name="' + this.options.fieldNames.channels + '"]').val();

        this.saveMessage(messageData.id, messageData, this.onMessageSavedResult.bind(this, messageBlock, messageData));
    },

    /**
     * Event: A message has been saved.
     */
    onMessageSavedResult: function(messageBlock, messageData, success)
    {
        // If the save has succeeded, we need to update the block with the new ID and message name, and their references
        if(success) {
            messageBlock.attr('data-name', messageData.id);
            messageBlock.attr('id', 'message-' + messageData.id);

            messageBlock.find('[data-action="saveMessage"]').attr('data-target', "#message-" + messageData.id);
            messageBlock.find('[data-action="deleteMessage"]').attr('data-target', "#message-" + messageData.id);

            messageBlock.removeClass('not-saved');
            $.notify({ message: "Message has been saved." });
        }
        else
            $.notify({ message: "An error occured during message save." }, { type: "danger" });
    },

    /// ACTIONS ///

    saveMessage: function(messageId, messageData, callback)
    {
        $.ajax({
            url: Routing.generate(this.options.saveRoute, {id: messageId}, true),
            type: 'post',
            data: messageData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                let data = jqXHR.responseJSON;
                callback(textStatus == "success" && data === true);
            }
        });
    },

    deleteMessage: function(messageId, callback)
    {
        $.ajax({
            url: Routing.generate(this.options.deleteRoute, {id: messageId}, true),
            type: 'get',
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                let data = jqXHR.responseJSON;
                callback(textStatus == "success" && data === true);
            }
        });
    },

    addMessage: function()
    {
        let newMessageBlock = this.elements.messageTemplate.clone();
        newMessageBlock.addClass('not-saved');
        newMessageBlock.attr("id", "#message-" + this.makeID(10)); // Set the ID to a random one

        $('select', newMessageBlock).removeClass('ms').selectpicker();

        this.elements.messageContainer.append(newMessageBlock);
    },

    makeID: function(length)
    {
        let text = "";
        const possible = "0123456789";
      
        for (let i = 0; i < length; i++)
          text += possible.charAt(Math.floor(Math.random() * possible.length));
      
        return text;
    }
};
var Announcements = {

    defaultOptions: {
        /**
         * @var {string|null} Selector pointing to the messages list container
         */
        messageContainerSelector: null,

        /**
         * @var {string|null} Selector for one message, inside the message container
         */
        messageSelector: null,

        /**
         * @var {string|null} Selector to the message template element
         */
        messageTemplateSelector: null,

        /**
         * @var {string|null} The selector for the "add message" button
         */
        addMessageSelector: null,

        /**
         * @var {string|null} Route to delete a message on the controller
         */
        deleteMessageRoute: null,

        /**
         * @var {string|null} Route to save a message on the controller
         */
        saveMessageRoute: null,

        /**
         * @var object Field names for each message property value
         */
        messageFieldNames: {
            message: null,
            channels: null
        },

        /**
         * @var {string|null} Selector pointing to the interval list container
         */
        intervalContainerSelector: null,
        
        /**
         * @var {string|null} Selector for one interval, inside the interval container
         */
        intervalSelector: null,
        
        /**
         * @var {string|null} Route to save an interval on the controller
         */
        saveIntervalRoute: null,

        /**
         * @var object Field names for each interval property value
         */
        intervalFieldNames: {
            enabled: null,
            channel: null,
            time: null,
            messages: null
        },
    },

    options: {},
    elements: {
        messageContainer: null,
        addButton: null,
        messageTemplate: null,
        intervalContainer: null
    },

    /**
     * Initializes the module
     * 
     * @param {array} options The option list
     */
    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();

        this.elements.intervalContainer.find(this.options.intervalSelector).each((function(index, el)
        {
            $(el).find('[name="' + this.options.intervalFieldNames.enabled + '"]').trigger('change');
        }).bind(this));
    },

    /**
     * Initializes the elements.
     */
    initElements: function()
    {
        this.elements.messageContainer = $(this.options.messageContainerSelector);
        this.elements.addButton = $(this.options.addMessageSelector);
        this.elements.messageTemplate = $(this.options.messageTemplateSelector);
        this.elements.intervalContainer = $(this.options.intervalContainerSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        this.elements.messageContainer.on('click', '[data-action="save"]', this.onSaveMessageClick.bind(this));
        this.elements.messageContainer.on('click', '[data-action="delete"]', this.onDeleteMessageClick.bind(this));
        this.elements.addButton.on('click', this.onAddMessageClick.bind(this));

        this.elements.intervalContainer.on('click', '[data-action="save"]', this.onSaveIntervalClick.bind(this));
        this.elements.intervalContainer.on('change','input[name="' + this.options.intervalFieldNames.enabled + '"]', this.onIntervalEnabledChange.bind(this));
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
        if(messageBlock.length > 0) {
            this.deleteMessage(messageBlock.attr('id').replace('message-', ''), this.onMessageDeletedResult.bind(this, messageBlock));
        } else { // This is a new, not-yet-saved message, we just delete the matching parent message element
            $(ev.currentTarget).parents(this.options.messageSelector).remove();
        }
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
        var messageBlock = this.elements.messageContainer.find(ev.currentTarget.dataset.target);
        var messageData = {};

        // If the message block has not been found, then it's a new message and just find the parent block that matches the message selector
        if(messageBlock.length == 0)
            messageBlock = $(ev.currentTarget).parents(this.options.messageSelector);
        
        messageData.id = messageBlock.attr('id').replace('message-', '');
        messageData.message = messageBlock.find('[name="' + this.options.messageFieldNames.message + '"]').val();
        messageData.channels = messageBlock.find('[name="' + this.options.messageFieldNames.channels + '"]').val();

        if(!messageData.channels) {
            messageData.channels = [];
        }

        this.saveMessage(messageData.id, messageData, this.onMessageSavedResult.bind(this, messageBlock));
    },

    /**
     * Event: A message has been saved.
     */
    onMessageSavedResult: function(messageBlock, messageId, success)
    {
        // If the save has succeeded, we need to update the block with the new ID and message name, and their references
        if(success) {
            messageBlock.attr('data-name', messageId);
            messageBlock.attr('id', 'message-' + messageId);

            messageBlock.find('[data-action="saveMessage"]').attr('data-target', "#message-" + messageId);
            messageBlock.find('[data-action="deleteMessage"]').attr('data-target', "#message-" + messageId);

            messageBlock.removeClass('not-saved');
            $.notify({ message: "Message has been saved." });
        }
        else {
            $.notify({ message: "An error occured during message save." }, { type: "danger" });
        }
    },

    onSaveIntervalClick: function(ev)
    {
        var intervalBlock = this.elements.intervalContainer.find(ev.currentTarget.dataset.target);
        var intervalData = {};

        intervalData.enabled = intervalBlock.find('[name="' + this.options.intervalFieldNames.enabled + '"]').is(":checked");
        intervalData.channel = intervalBlock.find('[name="' + this.options.intervalFieldNames.channel + '"]').val();
        intervalData.time = intervalBlock.find('[name="' + this.options.intervalFieldNames.time + '"]').val();
        intervalData.messages = intervalBlock.find('[name="' + this.options.intervalFieldNames.messages + '"]').val();

        this.saveInterval(intervalData.channel, intervalData, this.onIntervalSavedResult.bind(this));
    },

    onIntervalEnabledChange: function(ev)
    {
        var checkbox = $(ev.currentTarget);
        var intervalBlock = checkbox.parents(this.options.intervalSelector);
        intervalBlock
            .find('input[name="' + this.options.intervalFieldNames.time + '"]')
            .attr('disabled', !checkbox.is(':checked'))
                .parents('.form-line').toggleClass('disabled', !checkbox.is(':checked'));
        intervalBlock
            .find('input[name="' + this.options.intervalFieldNames.messages + '"]')
            .attr('disabled', !checkbox.is(':checked'))
                .parents('.form-line').toggleClass('disabled', !checkbox.is(':checked'));
    },

    onIntervalSavedResult: function(success)
    {
        if(success) {
            $.notify({ message: "Interval has been saved." });
        } else {
            $.notify({ message: "An error occured during interval save." }, { type: "danger" });
        }
    },

    /// ACTIONS ///

    /**
     * @param messageId
     * @param messageData
     * @param callback
     */
    saveMessage: function(messageId, messageData, callback)
    {
        let routeParams = {};
        if(messageId) {
            routeParams = {id: messageId};
        }
        
        $.ajax({
            url: Routing.generate(this.options.saveMessageRoute, routeParams, true),
            type: 'post',
            data: messageData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                var data = jqXHR.responseJSON;
                var requestSucceeded = (textStatus == "success" && data !== false);

                // Set the message ID from the data if the request succeeded and it was a creation
                if(!messageId && requestSucceeded) {
                    messageId = data;
                    data = true;
                }
                
                callback(messageId, requestSucceeded);
            }
        });
    },

    deleteMessage: function(messageId, callback)
    {
        $.ajax({
            url: Routing.generate(this.options.deleteMessageRoute, {id: messageId}, true),
            type: 'get',
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                var data = jqXHR.responseJSON;
                callback(textStatus == "success" && data === true);
            }
        });
    },

    addMessage: function()
    {
        let newMessageBlock = this.elements.messageTemplate.clone();
        newMessageBlock.addClass('not-saved');

        $('select', newMessageBlock).removeClass('ms').selectpicker();

        this.elements.messageContainer.append(newMessageBlock);
    },

    /**
     * @param intervalChannel
     * @param intervalData
     * @param callback
     */
    saveInterval: function(intervalChannel, intervalData, callback)
    {
        $.ajax({
            url: Routing.generate(this.options.saveIntervalRoute, {channel: intervalChannel}, true),
            type: 'post',
            data: intervalData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                var data = jqXHR.responseJSON;
                var requestSucceeded = (textStatus == "success" && data !== false);
                callback(requestSucceeded);
            }
        });
    }
};
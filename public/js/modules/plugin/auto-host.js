let AutoHost = {

    defaultOptions: {
        /**
         * @var {string|null} Route to index on the controller
         */
        indexAutohostRoute: null,

        /**
         * @var {string|null}  Selector pointing to the host channel select container
         */
        hostChannelContainerSelector: null,

        /**
         * @var {string|null} Selector pointing to the host channel select
         */
        hostChannelSelector: null,

        /**
         * @var {string|null}  Selector pointing to the host channel configuration container
         */
        configurationContainerSelector: null,

        /**
         * @var {string|null}  Selector pointing to the host channel configuration
         */
        configurationSelector: null,

        /**
         * @var {string|null} Selector pointing to the hosted channels container
         */
        hostedContainerSelector: null,

        /**
         * @var {string|null} Selector pointing to the hosted channels
         */
        hostedSelector: null,

        /**
         * @var {string|null} Selector to the hosted channel template element
         */
        hostedTemplateSelector: null,

        /**
         * @var {string|null} The selector for the "add hosted channel" button
         */
        addHostedSelector: null,

        /**
         * @var object Field names for each host channel configuration property value
         */
        configurationFieldNames: {
            enabled: null,
            timeInterval: null,
            blackList: null,
            whiteList: null,
        },

        /**
         * @var object Field names for each hosted channel property value
         */
        hostedFieldNames: {
            enabled: null,
            hostedChannel: null,
            priority: null
        }
    },
    options: {},
    elements: {
        hostChannelContainer: null,
        configurationContainer: null,
        addButton: null,
        hostedTemplate: null,
        hostedContainer: null
    },

    /**
     * Initializes the module
     *
     * @param {Object} options The option list
     */
    init: function (options) {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();

        // Add trigger on toggle UI elements to react like radio button in DOM
        this.elements.configurationContainer.find(this.options.configurationSelector).each((function(index, el)
        {
            $(el).find('[name="' + this.options.configurationFieldNames.enabled + '"]').trigger('change');
        }).bind(this));
        this.elements.hostedContainer.find(this.options.hostedSelector).each((function(index, el)
        {
            $(el).find('[name="' + this.options.hostedFieldNames.enabled + '"]').trigger('change');
        }).bind(this));

        // Init priority (%) UI slider with appropriate I/O formats
        document.querySelectorAll('.priority-slider').forEach(function (slider) {
            let priority = slider.dataset.priority * 100;
            noUiSlider.create(slider, {
                format: {
                    to: function ( value ) {
                        return value;
                    },
                    from: function ( value ) {
                        return Math.trunc(value);
                    }
                },
                start: [priority],
                tooltips: true,
                step: 1,
                range: {
                    'min': 0,
                    'max': 100,
                }
            });
        });
    },

    /**
     * Initializes the elements.
     */
    initElements: function () {
        this.elements.hostChannelContainer = $(this.options.hostChannelContainerSelector);
        this.elements.configurationContainer = $(this.options.configurationContainerSelector);
        this.elements.addButton = $(this.options.addHostedSelector);
        this.elements.hostedTemplate = $(this.options.hostedTemplateSelector);
        this.elements.hostedContainer = $(this.options.hostedContainerSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function () {
        this.elements.hostChannelContainer.on('change', this.options.hostChannelSelector, this.onHostChannelChange.bind(this));

        this.elements.configurationContainer.on('click', '[data-action="save"]', this.onSaveConfigurationClick.bind(this));

        this.elements.addButton.on('click', this.onAddHostedClick.bind(this));
        this.elements.hostedContainer.on('click', '[data-action="save"]', this.onSaveHostedClick.bind(this));
        this.elements.hostedContainer.on('click', '[data-action="delete"]', this.onDeleteHostedClick.bind(this));
    },

    /// EVENTS ///

    /**
     * Event: Reload interface to display a new channel (configuration and hosted channels related)
     */
    onHostChannelChange: function(ev)
    {
        let select = $(ev.currentTarget);
        let routeParams = {};
        let selectData = {};

        selectData.selectedHost = select.val();

        const url = Routing.generate(this.options.indexAutohostRoute, routeParams, true);
        const form = $('<form action="' + url + '" method="post">' +
            '<input type="text" name="selectedHost" value="' + select.val() + '" />' +
            '</form>');
        $('body').append(form);
        form.submit();
    },

    /**
     * Event: Saves a host channel configuration
     */
    onSaveConfigurationClick: function(ev)
    {
        let configurationBlock = this.elements.configurationContainer.find(ev.currentTarget.dataset.target);
        let configurationData = {};

        configurationData.channel= configurationBlock.attr('id').replace('host-', '');
        configurationData.enabled = configurationBlock.find('[name="' + this.options.configurationFieldNames.enabled + '"]').is(":checked");
        configurationData.timeInterval = configurationBlock.find('[name="' + this.options.configurationFieldNames.timeInterval + '"]').val();
        configurationData.whiteList = configurationBlock.find('[name="' + this.options.configurationFieldNames.whiteList + '"]').val();
        configurationData.blackList = configurationBlock.find('[name="' + this.options.configurationFieldNames.blackList + '"]').val();

        this.saveConfiguration(configurationData, this.onConfigurationSavedResult.bind(this, configurationBlock));
    },

    /**
     * Event: A host channel configuration has been saved.
     * TODO : need translation with a symfony-related package to find
     */
    onConfigurationSavedResult: function(configurationBlock, channelId, success)
    {
        // If the save has succeeded, we need to update the block with the new ID and message name, and their references
        if(success) {
            configurationBlock.attr('data-name', channelId);
            configurationBlock.attr('id', 'host-' + channelId);

            configurationBlock.find('[data-action="saveHosted"]').attr('data-target', "#hosted-" + channelId);
            configurationBlock.find('[data-action="deleteHosted"]').attr('data-target', "#hosted-" + channelId);

            configurationBlock.removeClass('not-saved');
            $.notify({ message: "Host channel configuration has been saved." });
        }
        else {
            $.notify({ message: "An error occured during Host channel configuration save." }, { type: "danger" });
        }
    },

    /**
     * Event: the Add message button has been pressed
     */
    onAddHostedClick: function()
    {
        this.addHosted();
    },

    /**
     * Event: A hosted channel "delete" button has been pressed
     */
    onDeleteHostedClick: function(ev)
    {
        let hostedBlock = this.elements.hostedContainer.find(ev.currentTarget.dataset.target).not();
        let hostedData = {};

        // If this is an already saved hosted channel, there should be a valid target
        if(hostedBlock.length > 0) {
            hostedData.id = hostedBlock.attr('id').replace('hosted-', '');
            hostedData.channel = this.elements.hostedContainer.data('channel');
            hostedData.hosted = hostedBlock.find('[name="' + this.options.hostedFieldNames.hostedChannel + '"]').val();

            this.deleteHosted(hostedData, this.onHostedDeletedResult.bind(this, hostedBlock));
        } else { // This is a new, not-yet-saved hosted channel, we just delete the matching parent message element
            $(ev.currentTarget).parents(this.options.hostedSelector).remove();
        }
    },

    /**
     * Event: A hosted channel has been deleted.
     * TODO : need translation with a symfony-related package to find
     */
    onHostedDeletedResult: function(hostedBlock, success)
    {
        // Deletion has been successful, we remove the hosted channel block
        if(success) {
            hostedBlock.remove();
            $.notify({ message: "Hosted channel has been deleted." });
        }
        else
            $.notify({ message: "An error occured during hosted channel deletion." }, { type: "danger" });
    },

    /**
     * Event: Saves an hosted channel
     */
    onSaveHostedClick: function(ev)
    {
        let hostedBlock = this.elements.hostedContainer.find(ev.currentTarget.dataset.target);
        let hostedData = {};

        // If the hosted channel block has not been found,
        //     then it's a new message and just find the parent block that matches the message selector
        if(hostedBlock.length === 0)
            hostedBlock = $(ev.currentTarget).parents(this.options.hostedSelector);

        hostedData.id = hostedBlock.attr('id').replace('hosted-', '');
        hostedData.channel = this.elements.hostedContainer.data('channel');
        hostedData.enabled = hostedBlock.find('[name="' + this.options.hostedFieldNames.enabled + '"]').is(":checked");
        hostedData.hosted = hostedBlock.find('[name="' + this.options.hostedFieldNames.hostedChannel + '"]').val();
        const priority = hostedBlock.find('[name="' + this.options.hostedFieldNames.priority + '"]').find('.noUi-tooltip').html();
        hostedData.priority = (priority / 100).toFixed(2);

        this.saveHosted(hostedData, this.onHostedSavedResult.bind(this, hostedBlock));
    },

    /**
     * Event: A channel hosted has been saved.
     * TODO : need translation with a symfony-related package to find
     */
    onHostedSavedResult: function(hostedBlock, hostedId, success)
    {
        // If the save has succeeded, we need to update the block with the new ID and message name, and their references
        if(success) {
            hostedBlock.attr('data-name', hostedId);
            hostedBlock.attr('id', 'hosted-' + hostedId);

            hostedBlock.find('[data-action="saveHosted"]').attr('data-target', "#hosted-" + hostedId);
            hostedBlock.find('[data-action="deleteHosted"]').attr('data-target', "#hosted-" + hostedId);

            hostedBlock.removeClass('not-saved');
            $.notify({ message: "Channel hosted has been saved." });
        }
        else {
            $.notify({ message: "An error occured during channel hosted save." }, { type: "danger" });
        }
    },

    /// ACTIONS ///

    /**
     * @param configurationData
     * @param callback
     */
    saveConfiguration: function(configurationData, callback)
    {
        let routeParams = {};

        $.ajax({
            url: Routing.generate(this.options.saveConfigurationRoute, routeParams, true),
            type: 'post',
            data: configurationData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                let data = jqXHR.responseJSON;
                const requestSucceeded = (textStatus === "success" && data !== false);

                callback(configurationData.channel, requestSucceeded);
            }
        });
    },

    /**
     * Add a new "data-empty" block to save a new hosted channel
     */
    addHosted: function()
    {
        let newHostedBlock = this.elements.hostedTemplate.clone();
        newHostedBlock.addClass('not-saved');

        $('select', newHostedBlock).removeClass('ms').selectpicker();

        this.elements.hostedContainer.append(newHostedBlock);
    },

    /**
     * @param hostedData
     * @param callback
     */
    saveHosted: function(hostedData, callback)
    {
        let routeParams = {};

        $.ajax({
            url: Routing.generate(this.options.saveHostedRoute, routeParams, true),
            type: 'post',
            data: hostedData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                let data = jqXHR.responseJSON;
                const requestSucceeded = (textStatus === "success" && data !== false);

                // Set the message ID from the data if the request succeeded and it was a creation
                if(!hostedData.id && requestSucceeded) {
                    hostedData.id = data;
                }

                callback(hostedData.id, requestSucceeded);
            }
        });
    },

    /**
     * @param hostedData
     * @param callback
     */
    deleteHosted: function(hostedData, callback)
    {
        let routeParams = {};

        $.ajax({
            url: Routing.generate(this.options.deleteHostedRoute, routeParams, true),
            type: 'post',
            data: hostedData,
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                const data = jqXHR.responseJSON;
                callback(textStatus === "success" && data === true);
            }
        });
    },

};
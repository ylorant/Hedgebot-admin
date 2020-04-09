var StreamControl = {
    defaultOptions: {
        /**
         * @var string Settings block selector
         */
        settingsBlockSelector: null,
        /**
         * @var string Settings form selector, inside the block
         */
        settingsFormSelector: null,

        /**
         * @var string Submit settings form button selector, from inside the form
         */
        submitSettingsSelector: null,

        /**
         * @var string Start ads button selector, from inside the block
         */
        startAdsSelector: null,

        /**
         * @var string Save settings route name
         */
        saveSettingsRoute: null,

        /**
         * @var string Start ads route name
         */
        startAdsRoute: null,

        /**
         * @var string Actions form selector, inside the block
         */
        actionsFormSelector: null,

        /**
         * @var string Channel to host input selector, from inside the form
         */
        hostChannelSelector: null,

        /**
         * @var string Hosting submit button selector, from inside the form
         */
        hostSubmitSelector: null,

        /**
         * @var string Channel to raid input seleector, from inside the form
         */
        raidChannelSelector: null,

        /**
         * @var string Raid submit button selector, from inside the form
         */
        raidSubmitSelector: null,

        /**
         * @var string Host route name
         */
        hostRoute: null,

        /**
         * @var string Raid route name
         */
        raidRoute: null
    },

    options: {},
    elements: {
        settingsBlock: null,
        settingsForm: null,
        actionsForm: null
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
    initElements: function()
    {
        this.elements.settingsBlock = $(this.options.settingsBlockSelector);
        this.elements.settingsForm = $(this.options.settingsFormSelector, this.elements.settingsBlock);
        this.elements.actionsForm = $(this.options.actionsFormSelector, this.elements.settingsBlock);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        $(this.options.submitSettingsSelector, this.elements.settingsForm).on('click', this.onSubmitSettingsClick.bind(this));
        $(this.options.startAdsSelector, this.elements.settingsBlock).on('click', this.onStartAdsClick.bind(this));
        $(this.options.hostSubmitSelector, this.elements.actionsForm).on('click', this.onHostSubmitClick.bind(this));
        $(this.options.raidSubmitSelector, this.elements.actionsForm).on('click', this.onRaidSubmitClick.bind(this));
    },

    onSubmitSettingsClick: function(ev)
    {
        var form = $(ev.target).parents(this.options.settingsBlockSelector).find(this.options.settingsFormSelector);
        var formValues = form.serializeArray();
        
        $.ajax({
            url: Routing.generate(this.options.saveSettingsRoute, {}, true),
            type: 'post',
            data: $.param(formValues),
            complete: this.onSettingsUpdateReply.bind(this)
        });

        return false;
    },

    onStartAdsClick: function()
    {
        var currentChannel = $('[name="channel"]', this.options.settingsFormSelector).val();

        $.ajax({
            url: Routing.generate(this.options.startAdsRoute, {channel: currentChannel}, true),
            type: 'post',
            data: {},
            complete: this.onStartAdsComplete.bind(this)
        });

        return false;
    },

    onHostSubmitClick: function(ev)
    {
        var form = $(ev.target).parents(this.options.settingsBlockSelector).find(this.options.actionsFormSelector);
        var targetChannel = form.find(this.options.hostChannelSelector).val();
        var currentChannel = $('[name="channel"]', this.options.settingsFormSelector).val();
        
        $.ajax({
            url: Routing.generate(this.options.hostRoute, {channel: currentChannel}, true),
            type: 'post',
            data: {target: targetChannel},
            complete: this.onHostChannelComplete.bind(this)
        });

        return false;
    },

    onRaidSubmitClick: function(ev)
    {

        var form = $(ev.target).parents(this.options.settingsBlockSelector).find(this.options.actionsFormSelector);
        var targetChannel = form.find(this.options.raidChannelSelector).val();
        var currentChannel = $('[name="channel"]', this.options.settingsFormSelector).val();
        
        $.ajax({
            url: Routing.generate(this.options.raidRoute, {channel: currentChannel}, true),
            type: 'post',
            data: {target: targetChannel},
            complete: this.onRaidChannelComplete.bind(this)
        });

        return false;
    },

    onSettingsUpdateReply: function(jqXHR, textStatus)
    {
        var data = jqXHR.responseJSON;
        if(data.success) {
            for(var i in data.info) {
                $('[name="' + i + '"]', this.elements.settingsForm).val(data.info[i]);
            }

            $.notify({message: "Updated stream settings."});
        } else {
            $.notify({message: "Failed updating stream settings"}, {type: "danger"});
        }
    },

    onStartAdsComplete: function(jqXHR, textStatus)
    {
        var data = jqXHR.responseJSON;

        if(data.success) {
            $.notify({message: "Ads started."});
        } else {
            $.notify({message: "Failed starting ads."}, {type: "danger"});
        }
    },

    onHostChannelComplete: function(jqXHR, textStatus)
    {
        var data = jqXHR.responseJSON;

        if(data.success) {
            $.notify({message: "Host command launched."});
        } else {
            $.notify({message: "Failed launching host command."}, {type: "danger"});
        }
    },

    onRaidChannelComplete: function(jqXHR, textStatus)
    {
        var data = jqXHR.responseJSON;

        if(data.success) {
            $.notify({message: "Raid command launched."});
        } else {
            $.notify({message: "Failed launching raid command."}, {type: "danger"});
        }
    }
};

$(function()
{
    var options = {
        settingsBlockSelector: '.stream-settings-widget',
        settingsFormSelector: '.stream-settings-form',
        submitSettingsSelector: 'button.update-btn',
        startAdsSelector: 'button.start-ads',
        actionsFormSelector: '.stream-settings-actions-form',
        hostChannelSelector: '.host-channel-name',
        hostSubmitSelector: '.host-channel-submit',
        raidChannelSelector: '.raid-channel-name',
        raidSubmitSelector: '.raid-channel-submit',

        saveSettingsRoute: 'streamcontrol_ajax_update_settings',
        startAdsRoute: 'streamcontrol_ajax_start_commercials',
        hostRoute: 'streamcontrol_ajax_host_channel',
        raidRoute: 'streamcontrol_ajax_raid_channel'
    };

    StreamControl.init(options);
});
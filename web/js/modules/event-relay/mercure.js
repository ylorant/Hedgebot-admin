var MercureClient = {
    defaultOptions: {
        /**
         * @var string Mercure configuration (keys: hubUrl, topic)
         */
        config: null,

        /**
         * @var EventManager Event manager instance
         */
        eventManager: null
    },

    options: {},

    /** @var EventManager Event manager instance */
    eventManager: null,

    eventSource: null,

    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);
        this.eventManager = this.options.eventManager;

        const url = new URL(this.options.config.hubUrl);
        url.searchParams.append('topic', this.options.config.topic);

        this.initEvents(url);
    },

    initEvents: function(url)
    {
        this.eventSource = new EventSource(url);
        this.eventSource.onmessage = this.onMessageReceived.bind(this);

        $(window).on('beforeunload', this.onUnload.bind(this));
    },

    onMessageReceived: function(e) 
    {
        var eventData = JSON.parse(e.data);
        this.eventManager.onEventReceived(eventData);
    },

    onUnload: function()
    {
        this.eventSource.close();
    }
};
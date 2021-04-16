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
    url: null,

    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);
        this.eventManager = this.options.eventManager;

        this.url = new URL(this.options.config.hubUrl);
        this.url.searchParams.append('topic', this.options.config.topic);

        this.initEvents();

        // Set an interval to reconnect each 10 minutes
        setInterval(this.onReconnectInterval.bind(this), 600000);
    },

    initEvents: function()
    {
        // Set the JWT as cookie before initializing the eventsource
        var esOptions = {};

        if(this.options.config.jwt) {
            esOptions = { 
                headers: {
                    'Authorization': 'Bearer ' + this.options.config.jwt
                }
            };
        }

        this.eventSource = new EventSourcePolyfill(this.url, esOptions);
        this.eventSource.onmessage = this.onMessageReceived.bind(this);
        this.eventSource.onerror = this.onError.bind(this);

        $(window).on('beforeunload', this.onUnload.bind(this));
    },

    onError: function(e)
    {
        this.eventManager.onEventError(e.error);
    },

    onMessageReceived: function(e) 
    {
        var eventData = JSON.parse(e.data);
        this.eventManager.onEventReceived(eventData);
    },

    onReconnectInterval: function(e)
    {
        this.eventSource.close();
        this.initEvents();
    },

    onUnload: function()
    {
        this.eventSource.close();
    }
};
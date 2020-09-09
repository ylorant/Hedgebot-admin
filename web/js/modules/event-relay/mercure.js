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
        // Set the JWT as cookie before initializing the eventsource
        var esOptions = {};

        if(this.options.config.jwt) {
            esOptions = { withCredentials: true };
            var expireDate = new Date(new Date().getTime() + 3600000 * 24 * 30);

            document.cookie = "mercureAuthorization=" + this.options.config.jwt + "; "
                            + "domain=" + url.host + "; "
                            + "path=/; "
                            + "expires=" + expireDate.toUTCString() + ";";
        }

        this.eventSource = new EventSource(url, esOptions);
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
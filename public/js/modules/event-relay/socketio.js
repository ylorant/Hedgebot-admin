var SocketIOClient = {
    defaultOptions: {
        /**
         * @var string Socket.IO configuration (keys: host)
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
    
    /** @var object Socket.IO instance */
    io: null,

    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);
        this.eventManager = this.options.eventManager;

        // Initialize only if Socket.IO is loaded
        if(typeof(io) !== "undefined") {
            this.io = io(this.options.config.host);
            this.initIOEvents();
        }
    },

    initIOEvents: function()
    {
        this.io.on('event', this.onEventReceived.bind(this));
    },

    bind: function(event, callback)
    {
        if(!(event in this.boundEvents)) {
            this.boundEvents[event] = [];
        }

        this.boundEvents[event].push(callback);
    },

    onEventReceived: function(data)
    {
        this.eventManager.onEventReceived(data);
    }
};
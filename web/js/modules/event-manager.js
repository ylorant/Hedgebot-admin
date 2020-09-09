var EventManager = {
    defaultOptions: {
    },

    options: {},

    boundEvents: {},
    
    /** @var object Socket.IO instance */
    io: null,

    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);
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
        console.log("Event received", data);
        var eventName = data.listener + "/" + data.event.name;
        if(eventName in this.boundEvents) {
            for(var i in this.boundEvents[eventName]) {
                this.boundEvents[eventName][i](data.event);
            }
        }

        var genericEvent = data.listener + "/*";
        if(genericEvent in this.boundEvents) {
            for(var i in this.boundEvents[genericEvent]) {
                this.boundEvents[genericEvent][i](data.event);
            }
        }
    }
};
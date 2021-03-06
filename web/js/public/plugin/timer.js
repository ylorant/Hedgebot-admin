var Timer = {
    defaultOptions: {
        /**
         * @var string Timer widget block selector.
         */
        timerBlockSelector: null,
        /**
         * @var string Timer time display block selector.
         */
        timerTimeDisplaySelector: null,
        /**
         * @var string Timer info element block selector.
         */
        timerInfoSelector: null
    },

    options: {},

    timeDiff: null,

    elements: {
        timerBlocks: null,
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
        this.bindRelayEvents();

        this.refreshAllTimers();
    },

    /**
     * Initializes the elements.
     */
    initElements: function()
    {
        this.elements.timerBlocks = $(this.options.timerBlockSelector);

        var remoteTimeElement = $(this.options.remoteTimeSelector);
        var remoteTime = remoteTimeElement.data('remote-time');
        var remoteMsec = remoteTimeElement.data('remote-msec');

        this.computeTimeDiff(remoteTime, remoteMsec);

        setInterval(this.refreshAllTimers.bind(this), 250);
    },

    /**
     * Binds events callbacks to the socket relay
     */
    bindRelayEvents: function()
    {
        EventManager.bind("timer/*", this.onTimerEvent.bind(this));
    },

    /**
     * Event: timer update from the bot
     */
    onTimerEvent: function(ev)
    {
        var timer = this.elements.timerBlocks.filter('[data-id="' + ev.timer.id + '"]');

        // Update remote time if present
        if(ev.localTime) {
            this.computeTimeDiff(ev.localTime, ev.msec);
        }

        if(timer.length) {
            this.updateTimerInfo(timer, ev.timer);
            this.refreshTimer(timer);
        }
    },

    /**
     * Refreshes completely all timers
     */
    refreshAllTimers: function()
    {
        this.elements.timerBlocks.each((function(index, element) {
            this.refreshTimer(element);
        }).bind(this));
    },

    /**
     * Refreshes completely the timer display classes
     */
    refreshTimer: function(timerElement)
    {
        timerElement = $(timerElement);

        var timerTimeBlock = $(this.options.timerTimeDisplaySelector, timerElement);
        var timerInfo = this.getTimerInfo(timerElement);

        timerElement.removeClass('timer-started timer-ended timer-paused');

        if(timerInfo.started) {
            timerElement.addClass('timer-started');
        } else if(timerInfo.offset != 0) {
            timerElement.addClass('timer-ended');
        }

        if(timerInfo.paused) {
            timerElement.addClass('timer-paused');
        } 

        var elapsed = this.getTimerElapsedTime(
            timerInfo.startTime, 
            timerInfo.offset, 
            timerInfo.paused, 
            timerInfo.started,
            timerInfo.countdown ? timerInfo.countdownAmount : null,
        );

        var timerFormattedTime = this.formatTimerTime(elapsed);
        timerTimeBlock.html(timerFormattedTime);

        // Fill player info
        var playerBlocks = $(this.options.playerSelector, timerElement);
        playerBlocks.each((function(index, playerElement) {
            var playerTimerBlock = $(this.options.playerTimeDisplaySelector, playerElement);
            var playerName = $(playerElement).data('id');
            
            playerTimerBlock.removeClass('timer-started timer-ended timer-paused');

            // Runner has stopped their timer
            if(timerInfo.players[playerName].elapsed) {
                playerTimerBlock.addClass('timer-ended');
                playerTimerBlock.html(this.formatTimerTime(timerInfo.players[playerName].elapsed));
            } else { // Timer is still running, we basically mimick general timer
                
                if(timerInfo.started) {
                    playerTimerBlock.addClass('timer-started');
                } else if(timerInfo.offset != 0) {
                    playerTimerBlock.addClass('timer-ended');
                }

                if(timerInfo.paused) {
                    playerTimerBlock.addClass('timer-paused');
                } 

                playerTimerBlock.html(timerFormattedTime);
            }
        }).bind(this));
    },

    /**
     * Updates a timer element info block with new data
     */
    updateTimerInfo: function(timerElement, data)
    {
        var timerInfoBlock = $(this.options.timerInfoSelector, timerElement);

        timerInfoBlock.data('start-time', data.startTime);
        timerInfoBlock.data('offset', data.offset);
        timerInfoBlock.data('paused', data.paused);
        timerInfoBlock.data('started', data.started);
        timerInfoBlock.data('countdown', data.countdown);
        timerInfoBlock.data('countdown-amount', data.countdownAmount);

        if(data.players) {
            for(var playerName in data.players) {
                var player = data.players[playerName];
                var playerInfoBlock = $(this.options.playerSelector, timerElement)
                    .filter('[data-id="' + playerName + '"]')
                    .find(this.options.playerInfoSelector);
                
                playerInfoBlock.data('elapsed', player.elapsed);
            }
        }
    },

    /**
     * Gets the timer info for a given timer block
     */
    getTimerInfo: function(timerElement)
    {
        var playersBlocks = $(this.options.playerSelector, timerElement);
        var timerInfoBlock = $(this.options.timerInfoSelector, timerElement);
        var timerInfo = {};

        timerInfo.startTime = timerInfoBlock.data('start-time');
        timerInfo.offset = timerInfoBlock.data('offset');
        timerInfo.paused = timerInfoBlock.data('paused');
        timerInfo.started = timerInfoBlock.data('started');
        timerInfo.countdown = timerInfoBlock.data('countdown');
        timerInfo.countdownAmount = timerInfoBlock.data('countdown-amount');
        timerInfo.players = [];

        playersBlocks.each((function(index, playerElement) {
            var playerInfoBlock = $(this.options.playerInfoSelector, playerElement);
            var playerName = $(playerElement).data('id');

            timerInfo.players[playerName] = {
                "player": playerName,
                "elapsed": playerInfoBlock.data('elapsed') || null
            };
        }).bind(this));

        return timerInfo;
    },

    /**
     * Gets the elapsed time on the given timer.
     */
    getTimerElapsedTime: function(startTime, offset = 0, paused = false, started = false, countdownAmount = null)
    {
        var elapsed = offset;

        if(started && !paused) {
            var now = new Date();
            var currentTimestamp = now.getTime() - this.timeDiff;
            elapsed += (currentTimestamp / 1000) - startTime;
        }

        if(countdownAmount != null) {
            elapsed = (countdownAmount) - elapsed;

            if(elapsed < 0) {
                elapsed = 0;
            }

        }
        
        return elapsed;
    },

    /**
     * Formats a timer's current time.
     */
    formatTimerTime: function(elapsed, milliseconds = false)
    {
        var output = "";

        var totalSeconds = Math.floor(elapsed);

        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor(totalSeconds / 60 - (hours * 60));
        var seconds = Math.floor(totalSeconds - (minutes * 60) - (hours * 3600));

        var components = [hours, minutes, seconds];
        components = components.map(function(el) {
            return el.toString().padStart(2, "0");
        });
        output = components.join(":");

        if(milliseconds) {
            var ms = Math.round(($elapsed - totalSeconds) * 1000);
            output += "." + ms;
        }
        
        return output;
    },

    /**
     * Computes and stores the time difference with the remote server
     */
    computeTimeDiff: function(time, msec)
    {
        var localTime = Date.now();
        var remoteTime = new Date(time);
        remoteTime.setMilliseconds(msec);

        this.timeDiff = localTime - remoteTime;
        
        console.log("Time diff w/ server is (msec) ", this.timeDiff);
    }
};
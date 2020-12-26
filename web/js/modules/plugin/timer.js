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
        timerInfoSelector: null,
        /**
         * @var string Timer start/stop button inside the timer block
         */
        startButtonSelector: null,
        /**
         * @var string The pause button inside the timer block
         */
        pauseButtonSelector: null,
        /**
         * @var string The reset button inside the timer block
         */
        resetButtonSelector: null,
        /**
         * @var string The player element selector, inside the timer block
         */
        playerSelector: null,
        /**
         * @var string The player info selector inside of the player block
         */
        playerInfoSelector: null,
        /**
         * @var string The player time display selector inside of the player block.
         */
        playerTimeDisplaySelector: null,
        /**
         * @var string The player stop button inside the player element
         */
        playerStopButtonSelector: null,
        /**
         * @var string The route to execute a timer action
         */
        actionRoute: null
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
        this.bindUIActions();
        this.bindRelayEvents();

        this.refreshAllTimers();
    },

    /**
     * Initializes the elements.
     */
    initElements: function()
    {
        this.elements.timerBlocks = $(this.options.timerBlockSelector);

        setInterval(this.refreshAllTimers.bind(this), 250);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        $(this.options.startButtonSelector, this.elements.timerBlocks).on('click', this.onStartButtonClick.bind(this));
        $(this.options.pauseButtonSelector, this.elements.timerBlocks).on('click', this.onPauseButtonClick.bind(this));
        $(this.options.resetButtonSelector, this.elements.timerBlocks).on('click', this.onResetButtonClick.bind(this));
        $(this.options.playerSelector + " " + this.options.playerStopButtonSelector, this.elements.timerBlocks).on('click', this.onPlayerStopButtonClick.bind(this));
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
            var localTime = Date.now();
            var remoteTime = new Date(ev.localTime);
            remoteTime.setMilliseconds(ev.msec);

            ev.timer.srvDiff = localTime - remoteTime;
            
            console.log("Time diff w/ server is (msec) ", ev.timer.srvDiff);
        }

        if(timer.length) {
            this.updateTimerInfo(timer, ev.timer);
            this.refreshTimer(timer);
        }
    },

    /**
     * Event: the start/stop button has been clicked
     */
    onStartButtonClick: function(ev)
    {
        var timerElement = $(ev.currentTarget).parents(this.options.timerBlockSelector);
        var timerId = timerElement.data('id');

        this.executeTimerAction(timerId, "start", timerElement);
    },

    /**
     * Event: the pause button has been clicked
     */
    onPauseButtonClick: function(ev)
    {
        var timerElement = $(ev.currentTarget).parents(this.options.timerBlockSelector);
        var timerId = timerElement.data('id');

        this.executeTimerAction(timerId, "pause", timerElement);
    },

    /**
     * Event: the reset button has been clicked
     */
    onResetButtonClick: function(ev)
    {
        var timerElement = $(ev.currentTarget).parents(this.options.timerBlockSelector);
        var timerId = timerElement.data('id');

        this.executeTimerAction(timerId, "reset", timerElement);
    },

    /**
     * Event: the stop button for a player has been clicked
     */
    onPlayerStopButtonClick: function(ev)
    {
        var timerElement = $(ev.currentTarget).parents(this.options.timerBlockSelector);
        var timerId = timerElement.data('id');
        var playerElement = $(ev.currentTarget).parents(this.options.playerSelector);
        var playerId = playerElement.data('id');

        this.executeTimerAction(timerId, "playerStop", timerElement, {"player": playerId});
    },

    /**
     * Executes an action on the given timer id, and then updates the timerElement on success.
     */
    executeTimerAction(timerId, action, timerElement, actionParameters)
    {
        if(typeof(actionParameters) == "undefined") {
            actionParameters = {};
        }

        $.ajax({
            url: Routing.generate(this.options.actionRoute, {timerId: timerId, action: action}, true),
            type: 'post',
            data: actionParameters,
            complete: function(jqXHR, textStatus)
            {
                var data = jqXHR.responseJSON;
                if(textStatus != "success" || !data) {
                    $.notify({ message: "An error occured during timer action." }, { type: "danger" });
                }
            }
        });
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
        // Get handles to required elements and timer info
        var timerTimeBlock = $(this.options.timerTimeDisplaySelector, timerElement);
        var startButton = $(this.options.startButtonSelector, timerElement);
        var pauseButton = $(this.options.pauseButtonSelector, timerElement);
        var timerInfo = this.getTimerInfo(timerElement);

        // Apply default settings to buttons (as if it wasn't started yet)
        timerTimeBlock.removeClass('col-grey col-green col-blue');
        startButton.attr('disabled', false);
        pauseButton.attr('disabled', false);
        startButton.find('i').html('play_arrow');
        pauseButton.find('i').html('pause');

        // Change the timer buttons and color depending on its status
        if(timerInfo.started) { // Timer running
            timerTimeBlock.addClass('col-blue');
            startButton.find('i').html('check');
        } else { // Timer is stopped/paused
            pauseButton.attr('disabled', true);
        
            // Timer has been stopped (finished)
            if(timerInfo.offset != 0) {
                timerTimeBlock.addClass('col-green');
                startButton.find('i').html('undo');
            }
        }
        
        // Timer is paused
        if(timerInfo.paused) {
            timerTimeBlock.addClass('col-grey');
            pauseButton.find('i').html('play_arrow');
            startButton.attr('disabled', true);
        } 

        var elapsed = this.getTimerElapsedTime(
            timerInfo.startTime, 
            timerInfo.offset, 
            timerInfo.paused, 
            timerInfo.started,
            timerInfo.srvDiff,
            timerInfo.countdown ? timerInfo.countdownAmount : null,
        );

        var timerFormattedTime = this.formatTimerTime(elapsed);
        timerTimeBlock.html(timerFormattedTime);

        // Fill player info
        var playerBlocks = $(this.options.playerSelector, timerElement);
        playerBlocks.each((function(index, playerElement) {
            var playerTimerBlock = $(this.options.playerTimeDisplaySelector, playerElement);
            var playerStopButton = $(this.options.playerStopButtonSelector, playerElement);
            var playerName = $(playerElement).data('id');
            
            playerTimerBlock.removeClass('col-grey col-green col-blue');
            playerStopButton.attr('disabled', false);
            playerStopButton.find('i').html('done');

            // If the timer is not currently in its running state, disable the stop button
            if(!timerInfo.started || timerInfo.paused) {
                playerStopButton.attr('disabled', true);
            }

            // Runner has stopped their timer
            if(timerInfo.players[playerName].elapsed) {
                playerTimerBlock.addClass('col-green');
                playerStopButton.find('i').html('undo');

                playerTimerBlock.html(this.formatTimerTime(timerInfo.players[playerName].elapsed));
            } else { // Timer is still running, we basically mimick general timer
                playerTimerBlock.addClass(timerTimeBlock.attr('class'));

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
        timerInfoBlock.data('srv-diff', data.srvDiff / 1000);

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
        timerInfo.srvDiff = timerInfoBlock.data('srv-diff') * 1000;
        timerInfo.players = {};

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
    getTimerElapsedTime: function(startTime, offset = 0, paused = false, started = false, srvDiff = 0, countdownAmount = null)
    {
        var elapsed = offset;

        if(started && !paused) {
            var now = new Date();
            var currentTimestamp = now.getTime() - srvDiff;
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
    }
};
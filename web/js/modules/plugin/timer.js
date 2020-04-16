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
         * @var string The route to execute a timer action
         */
        actionRoute: null
    },

    options: {},
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
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        $(this.options.startButtonSelector, this.elements.timerBlocks).on('click', this.onStartButtonClick.bind(this));
        $(this.options.pauseButtonSelector, this.elements.timerBlocks).on('click', this.onPauseButtonClick.bind(this));
        $(this.options.resetButtonSelector, this.elements.timerBlocks).on('click', this.onResetButtonClick.bind(this));

        setInterval(this.refreshAllTimers.bind(this), 1000);
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
     * Executes an action on the given timer id, and then updates the timerElement on success.
     */
    executeTimerAction(timerId, action, timerElement)
    {
        $.ajax({
            url: Routing.generate(this.options.actionRoute, {timerId: timerId, action: action}, true),
            type: 'post',
            data: {}
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
        var timerTimeBlock = $(this.options.timerTimeDisplaySelector, timerElement);
        var timerInfo = this.getTimerInfo(timerElement);
        var startButton = $(this.options.startButtonSelector, timerElement);
        var pauseButton = $(this.options.pauseButtonSelector, timerElement);

        timerTimeBlock.removeClass('col-grey col-green col-blue');
        startButton.attr('disabled', false);
        pauseButton.attr('disabled', false);
        startButton.find('i').html('play_arrow');
        pauseButton.find('i').html('pause');

        if(timerInfo.started) {
            timerTimeBlock.addClass('col-blue');
            startButton.find('i').html('check');
        } else {
            pauseButton.attr('disabled', true);
        
            if(timerInfo.offset != 0) {
                timerTimeBlock.addClass('col-green');
            }
        }
            

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
            timerInfo.countdown ? timerInfo.countdownAmount : null,
        );

        timerTimeBlock.html(this.formatTimerTime(elapsed));
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
        timerInfoBlock.data('countdownAmount', data.countdownAmount);
    },

    /**
     * Gets the timer info for a given timer block
     */
    getTimerInfo: function(timerElement)
    {
        var timerInfoBlock = $(this.options.timerInfoSelector, timerElement);
        var timerInfo = {};

        timerInfo.startTime = timerInfoBlock.data('start-time');
        timerInfo.offset = timerInfoBlock.data('offset');
        timerInfo.paused = timerInfoBlock.data('paused');
        timerInfo.started = timerInfoBlock.data('started');
        timerInfo.countdown = timerInfoBlock.data('countdown');
        timerInfo.countdownAmount = timerInfoBlock.data('countdown-amount');

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
            elapsed += (now.getTime() / 1000) - startTime;
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
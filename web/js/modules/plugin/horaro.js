var Horaro = {
    defaultOptions: {
        /**
         * @var string The schedule ident slug
         */
        identSlug: null,
        
        /**
         * @var string Next button selector
         */
        previousButtonSelector: null,

        /**
         * @var string Next button selector
         */
        pauseButtonSelector: null,

        /**
         * @var string Next button selector
         */
        nextButtonSelector: null,

        /**
         * @var string Paused schedule indicator selector
         */
        pausedScheduleSelector: null,

        /**
         * @var string Selector to the block containing actions, to toggle the waiting state on it when requests
         *             are performed.
         */
        controlBlockSelector: null,

        /**
         * @var string Route called to execute an action on the schedule
         */
        actionRoute: null,

        /**
         * @var string Route called to refresh the schedule
         */
        getScheduleRoute: null
    },

    options: {},
    elements: {
        buttons: {
            previous: null,
            pause: null,
            next: null
        },
        pausedScheduleBlock: null,
        controlBlock: null,
        loadingAnimation: null
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
    initElements: function(refresh)
    {
        this.elements.buttons.previous = $(this.options.previousButtonSelector);
        this.elements.buttons.pause = $(this.options.pauseButtonSelector);
        this.elements.buttons.next = $(this.options.nextButtonSelector);
        this.elements.pausedScheduleBlock = $(this.options.pausedScheduleSelector);
        this.elements.controlBlock = $(this.options.controlBlockSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        this.elements.buttons.previous.on('click', this.onPreviousButtonClick.bind(this));
        this.elements.buttons.pause.on('click', this.onPauseButtonClick.bind(this));
        this.elements.buttons.next.on('click', this.onNextButtonClick.bind(this));
    },

    onPreviousButtonClick: function(ev)
    {
        this.executeAction('previous');
        return false;
    },

    onPauseButtonClick: function(ev)
    {
        var pauseCallback = function()
        {
            this.refreshSchedule();
        };

        this.executeAction('pause', pauseCallback.bind(this));
        return false;
    },

    onNextButtonClick: function(ev)
    {
        this.executeAction('next');
        return false;
    },

    executeAction: function(action, cb)
    {
        this.loadingAnimation = this.elements.controlBlock.waitMe({
            effect: 'rotation',
            text: '',
            bg: 'rgba(255,255,255,0.90)',
            color: '#3f51b5'
        });

        $.ajax({
            url: Routing.generate(this.options.actionRoute, {identSlug: this.options.identSlug, action: action}, true),
            type: 'post',
            dataType: 'json',
            complete: function(jqXHR, textStatus)
            {
                if(cb) {
                    var data = jqXHR.responseJSON;
                    cb(textStatus == "success" && data === true);
                } else {
                    this.loadingAnimation.waitMe('hide');
                }
            }
        });
    },

    refreshSchedule: function()
    {
        var onScheduleGet = function(jqXHR, textStatus)
        {
            var schedule = jqXHR.responseJSON;
            this.elements.buttons.pause.text(schedule.paused ? 'Resume schedule' : 'Pause schedule');

            if(schedule.paused) {
                this.elements.pausedScheduleBlock.removeClass('hidden');
            } else {
                this.elements.pausedScheduleBlock.addClass('hidden');
            }

            this.loadingAnimation.waitMe('hide');
        };

        $.ajax({
            url: Routing.generate(this.options.getScheduleRoute, {identSlug: this.options.identSlug}, true),
            type: 'post',
            dataType: 'json',
            complete: onScheduleGet.bind(this)
        });
    }
};
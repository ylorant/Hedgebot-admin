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
         * @var string Selector to the schedule view HTML block. 
         */
        scheduleViewSelector: null,

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
        scheduleView: null
    },
    loadingAnimation: null,
    refreshScheduleInterval: null,

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

        this.refreshSchedule();
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
        this.elements.scheduleView = $(this.options.scheduleViewSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        this.elements.buttons.previous.on('click', this.onPreviousButtonClick.bind(this));
        this.elements.buttons.pause.on('click', this.onPauseButtonClick.bind(this));
        this.elements.buttons.next.on('click', this.onNextButtonClick.bind(this));

        this.refreshScheduleInterval = setInterval(this.refreshSchedule.bind(this), 30000);
    },

    onPreviousButtonClick: function(ev)
    {
        this.executeAction('previous', this.refreshSchedule.bind(this));
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
        this.executeAction('next', this.refreshSchedule.bind(this));
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

        var onComplete = function(jqXHR, textStatus)
        {
            if(cb) {
                var data = jqXHR.responseJSON;
                cb(textStatus == "success" && data === true);
            } else {
                this.loadingAnimation.waitMe('hide');
            }
        };

        $.ajax({
            url: Routing.generate(this.options.actionRoute, {identSlug: this.options.identSlug, action: action}, true),
            type: 'post',
            dataType: 'json',
            complete: onComplete.bind(this)
        });
    },

    refreshSchedule: function()
    {
        $.ajax({
            url: Routing.generate(this.options.getScheduleRoute, {identSlug: this.options.identSlug}, true),
            type: 'post',
            dataType: 'json',
            complete: this.updateScheduleView.bind(this)
        });
    },
    
    updateScheduleView: function(jqXHR, textStatus)
    {
        var schedule = jqXHR.responseJSON.schedule;
        var scheduleData = jqXHR.responseJSON.scheduleData;
        var scheduleTime = new Date(scheduleData.start);
        this.elements.buttons.pause.text(schedule.paused ? 'Resume schedule' : 'Pause schedule');

        if(schedule.paused) {
            this.elements.pausedScheduleBlock.removeClass('hidden');
        } else {
            this.elements.pausedScheduleBlock.addClass('hidden');
        }

        if(this.loadingAnimation) {
            this.loadingAnimation.waitMe('hide');
            this.loadingAnimation = null;
        }

        // Update schedule view
        $(this.elements.scheduleView).html('');

        // Write table header
        var thead = $('<thead></thead>');
        var headline = $('<tr></tr>');

        var columns = Array.from(scheduleData.columns);
        var firstElement = columns.shift();

        columns.unshift("Estimate");
        columns.unshift(firstElement);
        columns.unshift("Time");

        for(var i = 0; i < columns.length; i++) {
            headline.append('<th>' + columns[i] + '</th>');
        }

        thead.append(headline);
        $(this.elements.scheduleView).append(thead);

        var tbody = $('<tbody></tbody>');

        for(var i = 0; i < scheduleData.items.length; i++) {
            var item = scheduleData.items[i];
            var itemData = item.data;

            var itemFirstElement = itemData.shift();
            var timeString = scheduleTime.getHours().toString().padStart(2, "0") + ":" 
                           + scheduleTime.getMinutes().toString().padStart(2, "0");
            
            // Computing time
            var time = this.secondsToTime(item.length_t);
            itemData.unshift(time.hr.toString().padStart(2, "0") + ":" + 
                             time.mn.toString().padStart(2, "0") + ":" + 
                             time.sec.toString().padStart(2, "0"));
            itemData.unshift(itemFirstElement);
            itemData.unshift(timeString.padStart(2, "0"));

            var line = $('<tr></tr>');

            if(i == schedule.currentIndex) {
                line.addClass('info');
            }

            for(var j = 0; j < itemData.length; j++) {
                // Markdown
                var mdMatch = itemData[j].match(/\[(.+)\]\((.+)\)/);
                
                if(mdMatch != null) {
                    itemData[j] = '<a href="' + mdMatch[2] + '">' + mdMatch[1] + '</a>';
                }

                line.append($('<td>' + itemData[j] + '</td>'));
            }

            tbody.append(line);
        }

        $(this.elements.scheduleView).append(tbody);

        // debugger;
    },
    
    
    secondsToTime: function(time)
    {
        time = Math.abs(time);

        var minutes = 0;
        var hours = 0;
        var seconds = parseInt(time, 10);

        if(seconds >= 60)
            minutes = parseInt(seconds / 60, 10);
        if(minutes >= 60)
            hours = parseInt(minutes / 60, 10);

        seconds %= 60;
        minutes %= 60;

        return { hr: hours, mn: minutes, sec: seconds};
    }
};
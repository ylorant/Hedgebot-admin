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
         * @var string Refresh schedule button selector.
         */
        refreshDataButtonSelector: null,

        /**
         * @var string Paused schedule indicator selector
         */
        pausedScheduleSelector: null,

        /**
         * @var string Selector to the block containing actions, to toggle the waiting state on it when requests
         *             are performed.
         */
        controlBlockSelector: null,

        /** @var string Selector to the current item display HTML block */
        currentItemSelector: null,

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
        scheduleView: null,
        currentItem: null
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
        this.bindRelayEvents();

        this.refreshSchedule();
    },

    /**
     * Initializes the elements.
     */
    initElements: function()
    {
        this.elements.buttons.previous = $(this.options.previousButtonSelector);
        this.elements.buttons.pause = $(this.options.pauseButtonSelector);
        this.elements.buttons.next = $(this.options.nextButtonSelector);
        this.elements.buttons.refreshData = $(this.options.refreshDataButtonSelector);
        this.elements.pausedScheduleBlock = $(this.options.pausedScheduleSelector);
        this.elements.controlBlock = $(this.options.controlBlockSelector);
        this.elements.scheduleView = $(this.options.scheduleViewSelector);
        this.elements.currentItem = $(this.options.currentItemSelector);
    },

    bindRelayEvents: function()
    {
        EventManager.bind("horaro/*", this.onHoraroEvent.bind(this));
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function()
    {
        this.elements.buttons.previous.on('click', this.onPreviousButtonClick.bind(this));
        this.elements.buttons.pause.on('click', this.onPauseButtonClick.bind(this));
        this.elements.buttons.next.on('click', this.onNextButtonClick.bind(this));
        this.elements.buttons.refreshData.on('click', this.onRefreshDataButtonClick.bind(this));
        this.elements.scheduleView.on('click', '[data-action="goto"]', this.onGoToItemClick.bind(this));

        // this.refreshScheduleInterval = setInterval(this.refreshSchedule.bind(this), 30000);
    },

    onHoraroEvent: function(ev)
    {
        if(ev.schedule.identSlug == this.options.identSlug) {
            this.updateScheduleView(ev.schedule);
        }
    },

    onPreviousButtonClick: function(ev)
    {
        this.executeAction('previous');
        return false;
    },

    onPauseButtonClick: function(ev)
    {
        this.executeAction('pause');
        return false;
    },

    onNextButtonClick: function(ev)
    {
        this.executeAction('next');
        return false;
    },

    onRefreshDataButtonClick: function(ev)
    {
        this.executeAction('refreshData', (function() {
            $.notify({message: "Schedule update triggered, it will be updated shortly."});
            this.loadingAnimation.waitMe('hide');
        }).bind(this));
        return false;
    },

    onGoToItemClick: function(ev)
    {
        this.executeAction('goto', this.refreshSchedule.bind(this), {item: ev.currentTarget.dataset.item});
        return false;
    },

    executeAction: function(action, cb, parameters)
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
            } else if(this.loadingAnimation) {
                this.loadingAnimation.waitMe('hide');
            }
        };

        var routeParameters = {
            identSlug: this.options.identSlug,
            action: action
        };

        // Handling extra parameters
        if(typeof parameters == "object") {
            for(var i in parameters) {
                routeParameters[i] = parameters[i];
            }
        }

        $.ajax({
            url: Routing.generate(this.options.actionRoute, routeParameters, true),
            type: 'get',
            dataType: 'json',
            complete: onComplete.bind(this)
        });
    },

    refreshSchedule: function()
    {
        if(this.loadingAnimation) {
            this.loadingAnimation.waitMe('hide');
            this.loadingAnimation = this.elements.scheduleView.waitMe({
                effect: 'rotation',
                text: '',
                bg: 'rgba(255,255,255,0.90)',
                color: '#3f51b5'
            });
        }

        $.ajax({
            url: Routing.generate(this.options.getScheduleRoute, {identSlug: this.options.identSlug}, true),
            type: 'post',
            dataType: 'json',
            complete: (function(jqXHR) {
                this.updateScheduleView(jqXHR.responseJSON);
            }).bind(this)
        });
    },
    
    updateScheduleView: function(schedule)
    {
        var scheduleData = schedule.data;
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
        if(this.elements.scheduleView.length > 0) {
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
                    // Check if the column item is not empty, and if it is, use a placeholder string instead
                    if(!itemData[j]) {
                        itemData[j] = '-';
                    }

                    // Markdown
                    var mdMatch = itemData[j].match(/\[(.+?)\]\((.+?)\)/g);

                    if(mdMatch != null) {
                        for(var k = 0; k < mdMatch.length; k++) {
                            var mdItemMatch = mdMatch[k].match(/\[(.+?)\]\((.+?)\)/);
                            itemData[j] = itemData[j].replace(mdMatch[k], mdItemMatch[1]);
                        }
                    }

                    line.append($('<td>' + itemData[j] + '</td>'));
                }

                // Add the "go to item" button
                var button = $('<a data-action="goto" data-item="' + i + '"></a>');
                button.addClass('btn btn-primary');
                button.html('<i class="material-icons">fast_forward</i>')
                var cell = $('<td></td>');
                cell.append(button);
                line.append(cell);

                scheduleTime = new Date(scheduleTime.getTime() + 1000 * item.length_t + 1000 * scheduleData.setup_t);

                tbody.append(line);
            }

            $(this.elements.scheduleView).append(tbody);
        }

        if(this.elements.currentItem.length > 0) {
            var columns = Array.from(scheduleData.columns);

            $('[data-column]', this.elements.currentItem).each(function() {
                var columnIndex = $(this).data('column');
                var itemData = scheduleData.items[schedule.currentIndex].data[columnIndex];

                // Markdown
                var mdMatch = itemData.match(/\[(.+?)\]\((.+?)\)/g);

                if(mdMatch != null) {
                    for(var k = 0; k < mdMatch.length; k++) {
                        var mdItemMatch = mdMatch[k].match(/\[(.+?)\]\((.+?)\)/);
                        itemData = itemData.replace(mdMatch[k], mdItemMatch[1]);
                    }
                }

                $('.item-value', this).html(itemData);
            });
        }
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
    },

    getSimulateContext: function()
    {
        return {
            identSlug: $('input.ident-slug').val()
        };
    }
};
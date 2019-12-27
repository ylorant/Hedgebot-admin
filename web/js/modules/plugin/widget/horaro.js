$(function()
{
    var options = {
        identSlug: '',
        previousButtonSelector: '#previous-button',
        pauseButtonSelector: '#pause-button',
        nextButtonSelector: '#next-button',
        pausedScheduleSelector: '#schedule-paused',
        controlBlockSelector: '.schedule actions',
        actionRoute: 'horaro_ajax_schedule_action',
        getScheduleRoute: 'horaro_ajax_get_schedule',
        currentItemSelector: '#current-item-data',
        scheduleViewSelector: null
    };

    $('.schedule-card').each(function() {
        var scheduleSlug = $(this).data('slug');
        
        if(scheduleSlug) {
            options.identSlug = scheduleSlug;
            Horaro.init(options);
        }
    });
});
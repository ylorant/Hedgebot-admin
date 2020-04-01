$(function()
{
    var options = {
        timerBlockSelector: '.timer-widget',
        startButtonSelector: '.timer-start-button',
        pauseButtonSelector: '.timer-pause-button',
        resetButtonSelector: '.timer-reset-button',
        timerInfoSelector: '.timer-info',
        timerTimeDisplaySelector: '.timer-time',
        actionRoute: 'timer_ajax_action'
    };

    Timer.init(options);
});
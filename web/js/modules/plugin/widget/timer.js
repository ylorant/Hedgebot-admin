$(function()
{
    var options = {
        timerBlockSelector: '.timer-widget',
        startButtonSelector: '.timer-start-button',
        pauseButtonSelector: '.timer-pause-button',
        resetButtonSelector: '.timer-reset-button',
        timerInfoSelector: '.timer-info',
        timerTimeDisplaySelector: '.timer-time',
        playerSelector: '.timer-player',
        playerInfoSelector: '.timer-player-info',
        playerStopButtonSelector: '.timer-player-stop-button',
        playerTimeDisplaySelector: '.timer-player-time',
        actionRoute: 'timer_ajax_action'
    };

    Timer.init(options);
});
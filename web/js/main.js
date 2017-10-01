/**
 * Main Hedgebot module. Handles all basic functions.
 * @type {Object}
 */
var Hedgebot = {

    init: function()
    {
        $.notifyDefaults({
            placement: {
                from: "bottom",
                align: "center"
            },
            allow_dismiss: true,
            type: "info",
        });
    }
};

// Initialization function
$(function()
{
    RivetsFormatters.init(rivets);
    Hedgebot.init();
});

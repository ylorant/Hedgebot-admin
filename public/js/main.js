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

        $('input.datetimepicker').bootstrapMaterialDatePicker({ format : 'YYYY-MM-DD HH:mm' });
    }
};

// Initialization function
$(function()
{
    RivetsFormatters.init(rivets);
    Hedgebot.init();

    // Tooltips
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });

    // Display toggle
    $('[data-toggle="display"]').on('click', function(ev) {
        var button = $(ev.currentTarget);
        var target = $(button.data('target'));

        if(target.length) {
            target.toggleClass('hidden');
        }
    });
    
    // Initialize event manager and its relay client
    EventManager.init();

    switch(parameters.eventRelayConfig.type) {
        case "socketio":
            SocketIOClient.init({
                eventManager: EventManager,
                config: parameters.eventRelayConfig
            });
            break;
        
            case "mercure":
                MercureClient.init({
                    eventManager: EventManager,
                    config: parameters.eventRelayConfig
                });
                break;
    }
    
    // Load store autocomplete for all inputs that have the .store-autocomplete class
    Store.init({
        autocompleteInputSelector: '.store-autocomplete',
        fetchStoreRoute: 'store_get',
        fullContentTokenClass: 'full-token'
    });
});

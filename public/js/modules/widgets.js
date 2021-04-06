var Widgets = {

    /**
     * Module initialization.
     */
    init: function()
    {
        this.bindUIActions();
    },

    /**
     * Binds events to UI elements.
     */
    bindUIActions: function()
    {
        $('.widget [data-toggle="reload"]').on('click', this.updateWidget.bind(this));
    },

    /**
     * Widget update button callback. Updates the widget parent to which the event has been triggered from.
     *
     * @param e Event The event that has been triggered.
     */
    updateWidget: function(e)
    {
        var widget = $(e.target).parents('.widget');
        var widgetType = widget.data('type');
        var widgetId = widget.data('id');
        var card = $(e.target).parents('.card');
        var loading = null;

        // If the button is inside a card, show a waitMe inside it.
        if (card)
        {
            loading = card.waitMe({
                effect: 'rotation',
                text: '',
                bg: 'rgba(255,255,255,0.90)',
                color: '#3f51b5'
            });
        }

        // Call widget update PHP method.
        $.ajax({
            url: '/widget/' + widgetType + '/' + widgetId,
            method: 'GET',
        }).done(function(data)
        {
            // Update bound data using Rivets.js
            rivets.bind(widget, data);

            // Eventually hide the waitMe.
            if (loading)
                loading.waitMe('hide');
        });
    }
};

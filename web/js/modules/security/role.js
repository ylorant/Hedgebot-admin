var RoleActions = {
    defaultOptions: {
        rightsContainerSelector: null
    },

    rightsContainer: null,
    
    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();
    },

    initElements: function()
    {
        this.rightsContainer = $(this.options.rightsContainerSelector);
    },

    bindUIActions: function()
    {
        this.rightsContainer.on('change', '.right-row .override-right', this.onInheritRightChange.bind(this));

        this.rightsContainer.find('.right-row .override-right').trigger('change');
    },

    onInheritRightChange: function(ev)
    {
        var rightGrantEl = $(ev.target).parents('.right-row').find('.grant-right');
        
        if(ev.target.checked)
            rightGrantEl.removeAttr('disabled');
        else
            rightGrantEl.attr('disabled', true);
    }
};
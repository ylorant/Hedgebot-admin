var Twitter = {
    defaultOptions: {
        /**
         * @var string Selector for the trigger block container
         */
        triggerBlockContainerSelector: null,
        /**
         * @var string Selector to the trigger radio input, relative to the trigger block container
         */
        triggerInputSelector: null,
        /**
         * @var string Selector to the trigger block elements, relative to the trigger block container
         */
        triggerBlocksSelector: null,
        /**
         * @var string Constraint block container selector
         */
        constraintContainerSelector: null,
        /**
         * @var string Constraint type selector inside the constraint block
         */
        constraintTypeSelector: null,
        /**
         * @var stirng Constraint lval selector inside the constraint block
         */
        constraintLvalSelector: null
    },

    options: {},
    elements: {
        triggerInput: null,
        triggerBlockContainer: null,
        triggerBlocks: null,
        constraintContainer: null
    },

    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();

        this.onTriggerInputClick();
    },

    initElements: function()
    {
        this.elements.triggerBlockContainer = $(this.options.triggerBlockContainerSelector)
        this.elements.triggerInput = this.elements.triggerBlockContainer.find(this.options.triggerInputSelector);
        this.elements.triggerBlocks = this.elements.triggerInput.parents('.trigger-block-container').find('.trigger-block');
        this.elements.constraintContainer = $(this.options.constraintContainerSelector);
    },

    bindUIActions: function()
    {
        this.elements.triggerInput.on('click', this.onTriggerInputClick.bind(this));
        this.elements.constraintContainer.on('change', this.options.constraintTypeSelector, this.onConstraintTypeChange.bind(this));
    },

    onTriggerInputClick: function()
    {
        var triggerValue = this.elements.triggerInput.filter(':checked').val();

        this.elements.triggerBlocks.addClass('hidden', true)
                                   .find('input,textarea').attr('disabled', true);

        this.elements.triggerBlocks.filter('.' + triggerValue).removeClass('hidden')
                                   .find('input,textarea').attr('disabled', false);
    },

    onConstraintTypeChange: function(ev)
    {
        var element = $(ev.target);
        var val = element.val();
        
        $(ev.target)
            .parents('[data-item]')
            .find(this.options.constraintLvalSelector)
            .toggleClass('store-autocomplete full-token', val == 'store');
    }
};
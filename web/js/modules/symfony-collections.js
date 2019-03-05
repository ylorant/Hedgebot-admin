var SymfonyCollections =
{
    defaultOptions: {
        collectionSelector: '[data-prototype]', // Default collection selector will use the tags that have a prototype data attribute
        itemSelector: '[data-item]', // Default selector for the items in the collection
        addButtonSelector: '[data-action="collection-add"]', // Selector pointing to where the add button is located inside the location
        deleteButtonSelector: '[data-action="collection-delete"]', // Selector pointing to where the delete button is located inside a row
        newItemInsertSelector: null, // Selector pointing to where the new elements will be inserted. Is optional, if not given, new elements
                                     // will be inserted before the add button. If given, they'll be inserted before that element.
    },

    collectionElements: null,
    addButton: null,

    /**
     * Initializes the module
     */
    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();
    },

    /**
     * Initializes the referenced elements in the module
     */
    initElements: function()
    {
        this.collectionElements = $(this.options.collectionSelector);
    },

    /**
     * Binds the UI actions to the elements
     */
    bindUIActions: function()
    {
        // Closure needed for binding purposes
        var bindEvents = function(index, el)
        {
            $(el).on('click', this.options.addButtonSelector, this.onAddElement.bind(this));
            $(el).children(this.options.itemSelector).each(this.bindDeleteEvent.bind(this));
        };

        this.collectionElements.each(bindEvents.bind(this));
    },

    /**
     * Binds the delete event on the delete button found in the given element.
     */
    bindDeleteEvent: function(index, itemEl)
    {
        console.log("Binding delete event on ", itemEl);
        $(itemEl).find(this.options.deleteButtonSelector).on('click', this.onDeleteElement.bind(this));
    },

    /**
     * Handles a click on the Add button. Adds an element to the collection.
     */
    onAddElement: function(ev)
    {
        // To get the collection from the button, bubble up from it to the element that matches the collection selector
        var collection = $(ev.currentTarget).parents(this.options.collectionSelector);
        var counter = collection.data('item-counter');

        // If we can't find the stored item counter, we compute it from the actual elements
        if (!counter)
            counter = collection.children(':not(' + this.options.addButtonSelector + ')').length;
        
        // Generate the new element
        var newElement = collection.attr('data-prototype');
        newElement = newElement.replace(/__name__/g, counter);

        // Store the counter
        collection.data('item-counter', ++counter);

        // add the new element just before the add button
        var newElementDOM = $(newElement);
        var insertTarget = ev.currentTarget;

        if(this.options.newItemInsertSelector)
            insertTarget = collection.find(this.options.newItemInsertSelector);

        $(insertTarget).before(newElementDOM);

        this.bindDeleteEvent(counter, newElementDOM);

        $('select:not(.ms)', newElementDOM).selectpicker();
    },

    /**
     * Deletes an element from the collection
     */
    onDeleteElement: function(ev)
    {
        $(ev.currentTarget).parents(this.options.itemSelector).remove();
    }
};
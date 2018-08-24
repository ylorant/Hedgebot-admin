var Store = {
    defaultOptions: {
        autocompleteInputSelector: null,
        fetchStoreRoute: null,
    },
    options: {},
    
    elements: {
        focusedInput: null,
        autocompleteDropdown: null
    },

    store: {},
    currentSelectedOption: 0,
    isFetching: false,

    init: function(options)
    {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();
    },

    initElements: function()
    {

    },

    bindUIActions: function()
    {
        $(document).on('keydown click focus', this.options.autocompleteInputSelector, this.onKeypress.bind(this));
        $(document).on('blur', this.options.autocompleteInputSelector, this.onBlur.bind(this));

        // $(document).on('keydown', '.store-dropdown', this.onDropdownKeypress.bind(this));
        $(document).on('click', '.store-dropdown option', this.onDropdownClick.bind(this));
    },

    onKeypress: function(ev)
    {
        var input = ev.target;
        var caretPosition = input.selectionEnd;
        var inputValue = input.value;

        // If we're on a keydown even that has the keycode for left or right or backspace, we adapt the caret position
        // with the change that hasn't occured on the DOM yet. If the key is anything else that amounts to one character
        // we assume it's a key being typed and we add it (and move the caret position to reflect the added char).
        // Basically, this mimicks the effect of the keyboard on the actual input.
        if(ev.key) {
            if(ev.keyCode === 37 || ev.keyCode === 8) {
                caretPosition--;
            } else if(ev.keyCode === 39) {
                caretPosition++;
            } else if(ev.key.length === 1) {
                inputValue += ev.key;
                caretPosition++;
            }
        }
        
        // Get the current word being typed, relative to the caret position
        inputValue = inputValue.substring(0, caretPosition);
        var inputWords = inputValue.split(' ');
        var currentToken = inputWords.pop();

        // Check if we are trying to input a store variable (starts with $), and do not do anything if not
        if(currentToken[0] !== '$') {
            this.onBlur(ev);
            return;
        }

        // If we're still fetching the dropdown, do nothing
        if(this.isFetching) {
            return;
        }
        
        // Handle selection for the Dropdown if it is already open, and stop execution if it is appropriate
        if(ev.key) {
            var continueExec = this.onDropdownKeypress(ev);

            if(!continueExec) {
                ev.stopPropagation();
                return false;
            }
        }

        // From now on, refresh the dropdown

        // Strip the $ for autocompletion
        currentToken = currentToken.substring(1);
        
        // Get settings from field
        var storeSettings = this.getStoreSettingsFromDataAttributes(input);

        // Regenerate the dropdown and get the store if needed, or else just redo the options
        if(!this.elements.autocompleteDropdown) {
            this.generateDropdown(currentToken, storeSettings.basePath, storeSettings.namespace, storeSettings.context, storeSettings.channel);
            this.isFetching = true;
        } else {
            this.generateOptions(currentToken, storeSettings.basePath, storeSettings.namespace);
        }
        
        // Add the loader after the input
        $(input).after(this.elements.autocompleteDropdown);

        // Append loader into dropdown if store is being fetched
        if(this.isFetching) {
            var dropdownLoader = $('<div></div>');
            dropdownLoader.addClass('autocomplete-loader');
            this.elements.autocompleteDropdown.append(dropdownLoader);
        }

        // Position the dropdown
        var inputHeight = $(input).parents('.form-line').innerHeight();
        this.elements.autocompleteDropdown.css('top', inputHeight + "px");

        // Set the current input in the properties
        this.elements.focusedInput = input;
    },

    onBlur: function(ev)
    {   
        if(this.elements.autocompleteDropdown) {
            this.elements.autocompleteDropdown.remove();
        }
    },

    onDropdownKeypress: function(ev)
    {
        // User pressed enter
        if(ev.keyCode === 13) {
            // Get the current word being typed, relative to the caret position
            // TODO: mutualize that with the onKeypress version
            var inputValueLeft = this.elements.focusedInput.value.substring(0, this.elements.focusedInput.selectionEnd);
            var inputValueRight = this.elements.focusedInput.value.substring(this.elements.focusedInput.selectionEnd);
            var inputWords = inputValueLeft.split(' ');
            var currentToken = inputWords.pop();

            // Get the selected value and trim it to remove the part that is already written
            var selectedValue = this.elements.autocompleteDropdown.find('.autocomplete-option')
                                                                  .eq(this.currentSelectedOption).data('value');
            selectedValue = selectedValue.substring(currentToken.length - 1);

            $(this.elements.focusedInput).val(inputValueLeft + selectedValue + " " + inputValueRight);
            ev.target = this.elements.focusedInput;
            this.onBlur(ev);
            
            return false;
        }

        // User pressed up or down arrow
        if(ev.keyCode === 38 || ev.keyCode === 40) {
            // Only go up if we aren't on the first item (duh)
            if(ev.keyCode === 38 && this.currentSelectedOption > 0) {
                this.currentSelectedOption--;
            }

            // Only go down if we aren't on the last item
            if(ev.keyCode === 40 && this.currentSelectedOption + 1 < this.elements.autocompleteDropdown.find('.autocomplete-option').length) {
                this.currentSelectedOption++;
            }

            // Update CSS class of the active element
            var currentOptionElement = this.elements.autocompleteDropdown.find('.autocomplete-option')
                                                                         .eq(this.currentSelectedOption);

            this.elements.autocompleteDropdown.find('.autocomplete-option').removeClass('active');
            currentOptionElement.addClass('active');        

            // Scroll up or down if needed
            var dropdownScroll = this.elements.autocompleteDropdown.scrollTop();
            var dropdownHeight = this.elements.autocompleteDropdown.innerHeight();
            var currentElementPosition = currentOptionElement.position();
            var currentElementHeight = currentOptionElement.innerHeight();
            
            currentElementPosition.top += dropdownScroll;
            
            if(dropdownScroll > (currentElementPosition.top)) { 
                this.elements.autocompleteDropdown.scrollTop(currentElementPosition.top); // Scroll up to the option
            } else if(dropdownScroll + dropdownHeight <= currentElementPosition.top + currentElementHeight) {
                this.elements.autocompleteDropdown.scrollTop(currentElementPosition.top + currentElementHeight - dropdownHeight); // Scroll down to the element
            }
            
            return false;
        }

        return true;
    },

    // TODO: Implement this
    onDropdownClick: function(ev)
    {

    },

    generateDropdown: function(match, basePath, namespace, context, channel)
    {
        var dropdown = $('<div></div>');
        dropdown.addClass('store-dropdown card');
        dropdown.attr('multiple', true);

        var onStoreGet = function(store)
        {
            var choices = this.flattenStoreObject(store);
            this.store = choices;
            this.isFetching = false;

            this.generateOptions(match, basePath, namespace);
        };

        this.getStore(namespace, channel, context, onStoreGet.bind(this));

        this.elements.autocompleteDropdown = dropdown;

    },

    generateOptions: function(match, basePath, namespace)
    {
        this.elements.autocompleteDropdown.find('.autocomplete-option, .autocomplete-loader').remove();
        this.currentSelectedOption = 0;

        if(namespace) {
            basePath = namespace + '.' + basePath;
        }

        for(var i in this.store) {
            // Skip the store key if it doesn't correspond to the basepath
            if(basePath && !i.startsWith(basePath)) {
                continue;
            }
            
            // Generate the element and the different needed vars for the element
            var el = document.createElement('div');
            el.classList.add('autocomplete-option');
            var choiceValueString = this.store[i].toString();
            var itemKey = i;

            // Remove the basePath from the key since we don't need it for this input
            if(basePath) {
                itemKey = itemKey.substring(basePath.length + 1);
            }
            
            if(match && !itemKey.startsWith(match)) {
                continue;
            }
            
            el.dataset.value = itemKey;
            el.title = i; // Use full key as title to allow user to hover for full key title

            el.innerHTML = itemKey + ": " + choiceValueString;

            this.elements.autocompleteDropdown.append(el);
        }

        this.elements.autocompleteDropdown.find('.autocomplete-option').eq(this.currentSelectedOption).addClass('active');
    },

    getStore: function(namespace, channel, context, cb)
    {
        var params = {
            simulateData: true
        };

        if(namespace) {
            params.sourceNamespace = namespace;
        }

        if(channel) {
            params.channel = channel;
        }

        if(context) {
            params.simulateContext = context;
        }

        $.ajax({
            url: Routing.generate(this.options.fetchStoreRoute),
            data: params,
            type: "get",
            dataType: "json",
            success: function(data, textStatus) {
                if(textStatus == "success") {
                    cb(data);
                }
            }
        });
    },

    flattenStoreObject: function(store, basePath)
    {
        var flatStore = {};
        basePath = basePath ? basePath + "." : "";

        // Go through the current level of the store
        for(var i in store) {
            // Store element is an object, recurse the call and add the new elements to the store
            if(typeof store[i] === "object") {
                var flatSubElement = this.flattenStoreObject(store[i], basePath + i);
                
                for(var j in flatSubElement) {
                    flatStore[j] = flatSubElement[j];
                }
            } else {
                flatStore[basePath + i] = store[i];
                
                // Handle Markdown strings as their sub-objects too
                if(typeof store[i] === "string") {
                    var mdMatch = store[i].match(/\[(.+?)\]\((.+?)\)/g);
                    if(mdMatch != null) {
                        var mdTitle = store[i].toString();
                        var mdLink = store[i].toString();
    
                        // Replace markdown either by the link or the title depending on the subvar
                        for(var k = 0; k < mdMatch.length; k++) {
                            var mdItemMatch = mdMatch[k].match(/\[(.+?)\]\((.+?)\)/);
                            mdTitle = mdTitle.replace(mdMatch[k], mdItemMatch[1]);
                            mdLink = mdLink.replace(mdMatch[k], mdItemMatch[2]);
                        }
                
                        flatStore[basePath + i + '.title'] = mdTitle;
                        flatStore[basePath + i + '.link'] = mdLink;
                    }
                }
            }
        }

        return flatStore;
    },

    getStoreSettingsFromDataAttributes: function(input)
    {
        var settings = {
            "channel": null,
            "basePath": null,
            "namespace": null,
            "context": null
        };

        // The context setting can only be passed by value and will be used internally for other settings refs,
        // to specify in which context to look for the given selector. The context is searched in the parents of the
        // given input.
        var context = $('body');
        if(input.dataset.context) {
            context = $(input).parents(input.dataset.context);
        }

        // Here we will get store settings either by their direct value, or if it isn't present, by a reference to 
        // another input

        // Base path to restraint the list of values shown in the select
        if(input.dataset.basepath) {
            settings.basePath = input.dataset.basepath.toString();
        } else if(input.dataset.basepathRef) {
            settings.basePath = $(input.dataset.basepathRef, context).val().toString();
        }

        // Namespace restraint directly on the bot, to avoid drawing too much resources from it to get the store we need
        if(input.dataset.namespace) {
            settings.namespace = input.dataset.namespace.toString();
        } else if(input.dataset.namespaceRef) {
            settings.namespace = $(input.dataset.namespaceRef, context).val().toString();
        }

        // Channel specification, to guide the store providers to specialize their dataset
        if(input.dataset.channel) {
            settings.channel = input.dataset.channel.toString();
        } else if(input.dataset.channelRef) {
            settings.channel = $(input.dataset.channelRef, context).val().toString();
        }

        // Context specification
        if(input.dataset.simulateContext) {
            settings.context = input.dataset.simulateContext.toString();
        } else if(input.dataset.simulateContextRef) {
            settings.context = $(input.dataset.simulateContextRef, context).val().toString();
        } else if(input.dataset.simulateContextCallback) { // The settings are specified by a callback
            var funcPath = input.dataset.simulateContextCallback.split('.');
            var cb = window;

            // Iterate through the path from window to get the good function to call
            for(var i = 0; i < funcPath.length; i++) {
                if(cb[funcPath[i]]) {
                    cb = cb[funcPath[i]];
                } else {
                    cb = null;
                    break;
                }
            }

            // Call the function only if it has been found
            if(cb) {
                settings.context = cb();
            }
        }

        return settings;
    }
};
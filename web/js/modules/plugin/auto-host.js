let AutoHost = {

    defaultOptions: {
        /**
         * @var {string|null} Route to index on the controller
         */
        indexAutohostRoute: null,

        /**
         * @var {string|null}  Selector pointing to the host channel select container
         */
        hostChannelContainerSelector: null,

        /**
         * @var {string|null} Selector pointing to the host channel select
         */
        hostChannelSelector: null,

        /**
         * @var {string|null}  Selector pointing to the host channel configuration container
         */
        configurationContainerSelector: null,

        /**
         * @var {string|null}  Selector pointing to the host channel configuration
         */
        configurationSelector: null,

        /**
         * @var {string|null} Selector pointing to the hosted channels container
         */
        hostedContainerSelector: null,

        /**
         * @var {string|null} Selector pointing to the hosted channels
         */
        hostedSelector: null,

        /**
         * @var object Field names for each host channel configuration property value
         */
        configurationFieldNames: {
            channel: null,
            enabled: null,
            timeInterval: null,
            blackList: null,
            whiteList: null,
        },

        /**
         * @var object Field names for each hosted channel property value
         */
        hostedFieldNames: {
            enabled: null,
            hostedChannel: null,
            priority: null
        }
    },
    options: {},
    elements: {
        hostChannelContainer: null,
        configurationContainer: null,
        hostedContainer: null
    },

    /**
     * Initializes the module
     *
     * @param {array} options The option list
     */
    init: function (options) {
        this.options = $.extend(this.defaultOptions, options);

        this.initElements();
        this.bindUIActions();

        // Init priority (%) UI slider whit appropriate I/O formats
        document.querySelectorAll('.priority-slider').forEach(function (slider) {
            let priority = slider.dataset.priority * 100;
            noUiSlider.create(slider, {
                format: {
                    to: function ( value ) {
                        return value;
                    },
                    from: function ( value ) {
                        return Math.trunc(value);
                    }
                },
                start: [priority],
                tooltips: true,
                step: 1,
                range: {
                    'min': 0,
                    'max': 100,
                }
            });
        });
    },

    /**
     * Initializes the elements.
     */
    initElements: function () {
        this.elements.hostChannelContainer = $(this.options.hostChannelContainerSelector);
    },

    /**
     * Binds events from the elements to callbacks
     */
    bindUIActions: function () {
        this.elements.hostChannelContainer.on('change', this.options.hostChannelSelector, this.onHostChannelChange.bind(this));
    },

    /// EVENTS ///

    /**
     * Event:
     */
    onHostChannelChange: function(ev)
    {
        let select = $(ev.currentTarget);
        let routeParams = {};
        let selectData = {};

        selectData.selectedHost = select.val();
        $.ajax({
            url: 'http://hedgebot.lxc/app_dev.php/plugin/autohost',
            //url: Routing.generate(this.options.indexAutohostRoute, routeParams, true),
            type: 'post',
            data: selectData,
        });
    },

    /// ACTIONS ///

};
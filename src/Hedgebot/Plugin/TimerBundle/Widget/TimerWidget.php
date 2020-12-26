<?php
namespace Hedgebot\Plugin\TimerBundle\Widget;

use DateTime;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetInterface;
use Hedgebot\CoreBundle\Service\ApiClientService;
use Hedgebot\Plugin\TimerBundle\Helper\TimerHelper;

class TimerWidget implements DashboardWidgetInterface
{
    /** @var array Available colors for the timer widget */
    const COLORS = [
        '' => 'White',
        'bg-black' => 'Black',
        'bg-blue-grey' => 'Grey',
        'bg-red' => 'Red',
        'bg-orange' => 'Orange',
        'bg-amber' => 'Yellow',
        'bg-indigo' => 'Indigo',
        'bg-purple' => 'Purple'
    ];

    /** @var ApiClientService $hedgebotApi */
    protected $hedgebotApi;
    /** @var DateTime $remoteTime */
    protected static $remoteTime;

    public function __construct(ApiClientService $hedgebotApi)
    {
        $this->hedgebotApi = $hedgebotApi;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return "timer";
    }
    
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return "Timer control";
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return "Shows and controls a timer.";
    }

    /**
     * @inheritDoc
     */
    public function getViewName()
    {
        return 'HedgebotTimerBundle:widget:timer.html.twig';
    }

    /**
     * @inheritDoc 
     */
    public function getScriptPaths()
    {
        return [
            'js/modules/plugin/timer.js',
            'js/modules/plugin/widget/timer.js'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsFormType()
    {
        return TimerWidgetSettingsType::class;
    }

    /**
     * @inheritDoc
     */
    public function update(array $settings = [])
    {
        $endpoint = $this->hedgebotApi->endpoint('/plugin/timer');
        $timer = $endpoint->getTimerById($settings['timer']);

        // Creating the datetime for the remote time, but we can't directly use the ISO8601 shorthand
        
        if(empty(self::$remoteTime)) {
            $remoteTimeInfo = $endpoint->getLocalTime(); // Gets the remote time, despite the method name
            self::$remoteTime = DateTime::createFromFormat("Y-m-d\TH:i:sO v", join(' ', (array) $remoteTimeInfo));
        }
        

        $timer = TimerHelper::prepareTimer($timer, self::$remoteTime);

        return [
            'settings' => $settings,
            'timer' => $timer
        ];
    }
}
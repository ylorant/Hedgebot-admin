<?php
namespace Hedgebot\Plugin\TimerBundle\Widget;

use Hedgebot\CoreBundle\Interfaces\DashboardWidgetInterface;
use Hedgebot\CoreBundle\Service\ApiClientService;

class TimerWidget implements DashboardWidgetInterface
{
    /** @var ApiClientService $hedgebotApi */
    protected $hedgebotApi;

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

        // Format the time
        $timer->formattedTime = $this->formatTimerTime($timer);

        return [
            'settings' => $settings,
            'timer' => $timer
        ];
    }


    /**
     * Gets the elapsed time on the given timer.
     * 
     * @param stdClass $timer The timer to get the elapsed time for.
     * @return float The timer's elapsed time.
     */
    public function getTimerElapsedTime($timer)
    {
        $elapsed = $timer->offset;

        if($timer->started && !$timer->paused) {
            $elapsed += microtime(true) - $timer->startTime;
        }
        
        return $elapsed;
    }

    /**
     * Formats a timer's current time.
     * 
     * @param stdClass $timer The timer to get the formatted time of.
     * @param bool $milliseconds Wether to show milliseconds or not.
     * @return string The timer's time.
     */
    public function formatTimerTime($timer, bool $milliseconds = false)
    {
        $elapsed = $this->getTimerElapsedTime($timer);
        $output = "";
        
        if($timer->countdown && $timer->countdownAmount > 0) {
            $elapsed = $timer->countdownAmount - $elapsed;

            if($elapsed < 0) {
                $elapsed = 0;
            }
        }

        $totalSeconds = floor($elapsed);

        $hours = floor($totalSeconds / 3600);
        $minutes = floor($totalSeconds / 60 - ($hours * 60));
        $seconds = floor($totalSeconds - ($minutes * 60) - ($hours * 3600));

        $components = [$hours, $minutes, $seconds];
        $components = array_map(function($el) {
            return str_pad($el, 2, "0", STR_PAD_LEFT);
        }, $components);
        $output = join($components, ':');

        if($milliseconds) {
            $ms = round($elapsed - $totalSeconds, 3);
            $output .= ".". $ms;
        }
        
        return $output;
    }
}
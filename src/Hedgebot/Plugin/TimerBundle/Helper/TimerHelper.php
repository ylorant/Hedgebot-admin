<?php
namespace Hedgebot\Plugin\TimerBundle\Helper;

class TimerHelper
{
    /**
     * Gets the elapsed time on the given timer.
     * 
     * @param stdClass $timer The timer to get the elapsed time for.
     * @return float The timer's elapsed time.
     */
    public static function getTimerElapsedTime($timer)
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
    public static function formatTimerTime($timer, bool $milliseconds = false)
    {
        $elapsed = self::getTimerElapsedTime($timer);
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
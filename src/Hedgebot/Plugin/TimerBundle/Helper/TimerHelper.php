<?php
namespace Hedgebot\Plugin\TimerBundle\Helper;

class TimerHelper
{
    /**
     * Prepares a timer for displaying. It sets some variables with formatted data into it for easy access.
     * 
     * @param stdClass $timer Th timer to prepare.
     * @return stdClass The prepared timer.
     */
    public static function prepareTimer($timer)
    {
        // Format the time
        $timer->formattedTime = self::formatTimerTime($timer);

        if($timer->type == "race-timer") {
            // Set the players as an actual array to allow iterating on it from Twig
            $timer->players = (array) $timer->players;

            // Format the time for each player that has already finished
            foreach($timer->players as &$player) {
                if(!empty($player->elapsed)) {
                    $player->formattedTime = self::formatTimerTime($timer, $player->player);
                }
            }
        }

        return $timer;
    }

    /**
     * Gets the elapsed time on the given timer.
     * 
     * @param stdClass $timer The timer to get the elapsed time for.
     * @return float The timer's elapsed time.
     */
    public static function getTimerElapsedTime($timer, $player = null)
    {
        if(!empty($player)) {
            $elapsed = $timer->players[$player]->elapsed;
        } else {
            $elapsed = $timer->offset;
        }

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
    public static function formatTimerTime($timer, $player = null, bool $milliseconds = false)
    {
        $elapsed = self::getTimerElapsedTime($timer, $player);
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
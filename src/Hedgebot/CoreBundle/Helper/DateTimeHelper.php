<?php
namespace Hedgebot\CoreBundle\Helper;

class DateTimeHelper
{
    /**
     * @return array Time intervals and their divisors combos
     */
    private static function getTimeIntervals() {
        return [
            ['s', 1],
            ['m', 60],
            ['h', 3600],
            ['d', 86400]
        ];
    }

    /**
     * Converts an interval time from seconds to an human readable form (for example 15m17s).
     *
     * @param int $time The time in seconds to convert.
     *
     * @return string
     */
    public static function convertIntervalToHumanReadable($time)
    {
        $index = -1;
        $humanTime = "";
        $timeIntervalArray = self::getTimeIntervals();

        // Find the biggest interval to divide by
        $t = $time;
        while ($t > 1) {
            $t = $time / $timeIntervalArray[++$index][1];
        }

        // Go to the previous interval, to have something between 1 and its smaller division
        $index--;

        // Create the string from the time in seconds
        $intervalSums = 0;
        for ($i = $index; $i >= 0; $i--) {
            $timeInterval = floor(($time - $intervalSums) / $timeIntervalArray[$i][1]);
            $intervalSums += $timeInterval * $timeIntervalArray[$i][1];

            if ($timeInterval > 0) {
                $humanTime .= $timeInterval . $timeIntervalArray[$i][0];
            }
        }

        return $humanTime;
    }

    /**
     * Converts an human-readable time interval to its representation in seconds.
     *
     * @param string $humanTime The time interval in human readable form.
     *
     * @return int|bool The time in seconds if successful, false if the input time is malformed.
     */
    public static function convertHumanReadableToTime($humanTime)
    {
        $time = 0;
        $timeIntervalArray = self::getTimeIntervals();
        $modifiers = join("", array_column($timeIntervalArray, 0));
        $pattern = "/([0-9]+[" . $modifiers . "])/";

        // Match and get the multipliers on the human time
        $matchesCount = preg_match_all($pattern, $humanTime, $matches);
        if ($matchesCount == 0) {
            return false;
        }

        // Go through matches and apply multipliers
        foreach ($matches[1] as $value) {
            $modifierInfo = null;
            $modifier = substr($value, -1);

            foreach ($timeIntervalArray as $m) {
                if ($m[0] == $modifier) {
                    $modifierInfo = $m;
                    break;
                }
            }

            $time += intval(substr($value, 0, -1)) * $modifierInfo[1];
        }

        return $time;
    }
}
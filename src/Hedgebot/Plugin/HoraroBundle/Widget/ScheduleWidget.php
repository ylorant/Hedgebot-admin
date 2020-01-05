<?php
namespace Hedgebot\Plugin\HoraroBundle\Widget;

use DateInterval;
use DateTime;
use Hedgebot\CoreBundle\Interfaces\DashboardWidgetInterface;
use Hedgebot\CoreBundle\Service\ApiClientService;

class ScheduleWidget implements DashboardWidgetInterface
{
    /** @vat HedgebotClient $hedgebotApi */
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
        return "horaro-schedule";
    }
    
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return "Horaro Schedule Widget";
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return "Allows controlling the current schedule on the selected channel";
    }

    /**
     * @inheritDoc
     */
    public function getViewName()
    {
        return 'HedgebotHoraroBundle:widget:schedule.html.twig';
    }

    /**
     * @inheritDoc 
     */
    public function getScriptPaths()
    {
        return [
            'js/modules/plugin/horaro.js',
            'js/modules/plugin/widget/horaro.js'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsFormType()
    {
        return ScheduleWidgetSettingsType::class;
    }

    /**
     * @inheritDoc
     */
    public function update(array $settings = [])
    {
        $schedule = null;
        $scheduleData = null;

        $endpoint = $this->hedgebotApi->endpoint('/plugin/horaro');
        
        // Try to get if there's a schedule actually on
        $schedules = (array) $endpoint->getSchedules();
        $schedulesData = (array) $endpoint->getSchedulesData();
        $activeSlug = $this->findActiveSchedule($settings['channel'], $schedules, $schedulesData);
        
        // Get the schedule data
        if(!empty($activeSlug)) {
            $schedule = $endpoint->getSchedule($activeSlug);
            $scheduleData = $endpoint->getScheduleData($activeSlug);
        }

        return [
            'settings'     => $settings,
            'schedule'     => $schedule,
            'scheduleData' => $scheduleData
        ];
    }

    /**
     * Tries to find the active schedule from the given schedules.
     * @param string $channel The channel to find the active schedule of.
     * @param array  $schedules The list of available schedules.
     * @param array  $schedulesData The list of available schedules' data.
     * @return string|null The schedule ident slug if found, null if no schedule is active.
     */
    protected function findActiveSchedule($channel, $schedules, $schedulesData)
    {
        $startTimes = [];
        $now = new DateTime();
        $dayDelay = new DateInterval('P1D');

        foreach($schedules as $schedule) {
            if(!$schedule->enabled || $schedule->channel != $channel) {
                continue;
            }

            $scheduleData = $schedulesData[$schedule->identSlug];
            $scheduleStartTime = new DateTime($scheduleData->start);
            
            // Remove 1 hr from the schedule start time to show it slightly before it starts
            $scheduleStartTime->sub(new DateInterval("PT1H"));

            // If the start time is in the future, discard it
            if($scheduleStartTime > $now) {
                continue;
            }

            // Limit the end of the schedule as the last item end timee + 1 day
            $lastItem = end($scheduleData->items);
            $endDate = new DateTime($lastItem->scheduled);
            $lastItemDuration = new DateInterval($lastItem->length);
            $endDate->add($lastItemDuration);
            $endDate->add($dayDelay);

            if($endDate < $now) {
                continue;
            }

            $startTimes[$schedule->identSlug] = $scheduleData->start_t;
        }

        if(!empty($startTimes)) {
            asort($startTimes);
            $orderedSlugs = array_keys($startTimes);
            $selectedSlug = reset($orderedSlugs);

            return $selectedSlug;
        }

        return null;
    }
}
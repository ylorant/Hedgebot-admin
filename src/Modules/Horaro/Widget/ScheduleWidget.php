<?php
namespace App\Modules\Horaro\Widget;

use App\Interfaces\DashboardWidgetInterface;
use App\Service\ApiClientService;

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
        return 'horaro/widget/schedule.html.twig';
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
        $scheduleData = null;
        $endpoint = $this->hedgebotApi->endpoint('/plugin/horaro');

        // Try to get if there's a schedule actually on
        $activeSchedule = $endpoint->getCurrentSchedule($settings['channel'], true);

        // Get the schedule data
        if (!empty($activeSchedule)) {
            $scheduleData = $endpoint->getScheduleData($activeSchedule->identSlug);
        }

        return [
            'settings'     => $settings,
            'schedule'     => $activeSchedule,
            'scheduleData' => $scheduleData
        ];
    }
}

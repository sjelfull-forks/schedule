<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\console;

use Craft;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\Plugin;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Class ScheduleController
 *
 * @package panlatent\schedule\console
 * @author Panlatent <panlatent@gmail.com>
 */
class SchedulesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * List all schedules.
     */
    public function actionList()
    {
        $schedules = Plugin::$plugin->getSchedules();

        $i = 0;
        if ($ungroupedSchedules = $schedules->getSchedulesByGroupId()) {
            $this->stdout(Craft::t('schedule', 'Ungrouped') . ": \n", Console::FG_YELLOW);
            foreach ($ungroupedSchedules as $schedule) {
                /** @var Schedule $schedule */
                $this->stdout(Console::renderColoredString("    > #{$i} %c{$schedule->handle}\n"));
                ++$i;
            }
        }

        foreach ($schedules->getAllGroups() as $group) {
            $this->stdout("{$group->name}: \n", Console::FG_YELLOW);
            foreach ($group->getSchedules() as $schedule) {
                /** @var Schedule $schedule */
                $this->stdout(Console::renderColoredString("    > #{$i} %c{$schedule->handle}\n"));
                ++$i;
            }
        }
    }

    /**
     * Run all schedules.
     */
    public function actionRun()
    {
        $events = Plugin::$plugin->getBuilder()
            ->build()
            ->dueEvents(Craft::$app);

        if (empty($events)) {
            $this->stdout("No scheduled commands are ready to run.\n");
            return;
        }

        foreach ($events as $event) {
            $command = $event->getSummaryForDisplay();
            $this->stdout("Running scheduled command: {$command}\n");

            $event->run(Craft::$app);

            Craft::info("Running scheduled command: {$command}", __METHOD__);
        }

        Craft::info("Running scheduled event total: " . count($events), __METHOD__);
    }
}
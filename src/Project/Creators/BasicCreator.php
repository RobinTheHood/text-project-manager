<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use DateTime;
use RobinTheHood\TextProjectManager\Project\ProjectParser;

class BasicCreator
{
    private function getHoursFromSubTasks(array $subTasks): float
    {
        $totalHours = 0;
        foreach ($subTasks as $subTask) {
            $hours = 0;
            if ($subTask['quantity']['unit'] == 'min') {
                $hours = 1 / 60 * $subTask['quantity']['value'];
            } elseif ($subTask['quantity']['unit'] == 'h') {
                $hours = $subTask['quantity']['value'];
            }
            $totalHours += $hours;
        }
        return $totalHours;
    }

    private function getFirstSubTaskFromSubTasksByDate(array $subTasks): array
    {
        $firstSubTask = null;
        foreach ($subTasks as $subTask) {
            if (!$firstSubTask || $this->compareDate($firstSubTask['date'], $subTask['date']) > 0) {
                $firstSubTask = $subTask;
            }
        }
        return $firstSubTask;
    }

    private function getLastSubTaskFromSubTasksByDate(array $subTasks): array
    {
        $lastSubTask = null;
        foreach ($subTasks as $subTask) {
            if (!$lastSubTask || $this->compareDate($lastSubTask['date'], $subTask['date']) < 0) {
                $lastSubTask = $subTask;
            }
        }
        return $lastSubTask;
    }

    private function filterBillableSubTasks(array $subTasks): array
    {
        $billableSubTasks = [];
        foreach ($subTasks as $subTask) {
            if ($subTask['status'] !== ProjectParser::SUBTASK_STATUS_BILLABLE) {
                continue;
            }
            $billableSubTasks[] = $subTask;
        }
        return $billableSubTasks;
    }

    private function filterUnbillableSubTasks(array $subTasks): array
    {
        $unbillableSubTasks = [];
        foreach ($subTasks as $subTask) {
            if ($subTask['status'] !== ProjectParser::SUBTASK_STATUS_UNBILLABLE) {
                continue;
            }
            $unbillableSubTasks[] = $subTask;
        }
        return $unbillableSubTasks;
    }

    private function getFormatedSubTaskNamesFromSubTask(array $subTasks): string
    {
        $string = '';
        foreach ($subTasks as $subTask) {
            $string .= "- {$subTask['name']} ({$subTask['date']})\n";
        }
        return $string;
    }

    private function getBillableSubTasksFromTask(array $task): array
    {
        return $this->filterBillableSubTasks($task['subTasks']);
    }

    private function getUnbillableSubTasksFromTask(array $task): array
    {
        return $this->filterUnbillableSubTasks($task['subTasks']);
    }

    private function compareDate(string $germanDate1, string $germanDate2): int
    {
        $dateTime1 = new DateTime($germanDate1);
        $dateTime2 = new DateTime($germanDate2);

        if ($dateTime1->getTimestamp() < $dateTime2->getTimestamp()) {
            return -1;
        } elseif ($dateTime1->getTimestamp() > $dateTime2->getTimestamp()) {
            return 1;
        }

        return 0;
    }

    private function stepRoundHours(float $hours, float $step): float
    {
        $minutes = round($hours * 60);
        $minutesStep = round($step * 60);
        return $this->stepRoundMinutes($minutes, $minutesStep) / 60;
    }

    private function stepRoundMinutes(float $minutes, float $step): float
    {
        $roundedMinutes = round($minutes / $step) * $step;
        return $roundedMinutes;
    }
}

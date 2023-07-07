<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use DateTime;
use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Entities\Quantity;
use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

abstract class AbstractCreator
{
    protected function clamp($value, $min, $max)
    {
        if ($value < $min) {
            return $min;
        } elseif ($value > $max) {
            return $max;
        } else {
            return $value;
        }
    }

    /**
     * @return Report[]
     */
    protected function getAllReportsFromTask(Task $task): array
    {
        $reports = [];
        foreach ($task->users as $user) {
            $reports = array_merge($reports, $user->repors);
        }
        return $reports;
    }

    protected function formatCurrency(float $value): string
    {
        return number_format($value, 2, ',', '.') . ' €';
    }

    protected function formatHours(float $value): string
    {
        return str_replace('.', ',', '' . $value) . ' Std.';
    }

    protected function getFirstReportByDate(array $reports): Report
    {
        /**
         * @var Report
         */
        $firstReport = null;
        foreach ($reports as $report) {
            if (!$firstReport || $this->compareDate($firstReport->date, $report->date) > 0) {
                $firstReport = $report;
            }
        }
        return $firstReport;
    }

    protected function getLastReportByDate(array $reports): Report
    {
        /**
         * @var Report
         */
        $lastReport = null;
        foreach ($reports as $report) {
            if (!$lastReport || $this->compareDate($lastReport->date, $report->date) < 0) {
                $lastReport = $report;
            }
        }
        return $lastReport;
    }

    protected function compareDate(string $germanDate1, string $germanDate2): int
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
}

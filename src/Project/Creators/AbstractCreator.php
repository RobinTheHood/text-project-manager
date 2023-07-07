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
     * Lierfert den externel Preis eines Reports oder den standard externen Preis.
     */
    protected function getExternalPrice(?Report $report, float $basePrice): float
    {
        if (!$report) {
            return $basePrice;
        }

        if (!$report->externalPrice) {
            return $basePrice;
        }

        return $report->externalPrice->value;
    }

    /**
     * Liefert den internen Preis eines Reports oder den standard internen Preis.
     */
    protected function getInternalPrice(?Report $report, float $basePrice): float
    {
        if (!$report) {
            return $basePrice;
        }

        if (!$report->internalPrice) {
            return $basePrice;
        }

        return $report->internalPrice->value;
    }


    /**
     * Gruppiert Reports anhand ihres externen Preises.
     *
     * @param Report[] $reports
     */
    protected function groupReportsByExternalPrice(array $reports, float $basePrice): array
    {
        $groupedReports = [];
        foreach ($reports as $report) {
            $externalPrice = $this->getExternalPrice($report, $basePrice);
            $groupedReports[$externalPrice][] = $report;
        }
        return $groupedReports;
    }

    /**
     * Gruppiert Reports anhand ihres internen Preises.
     *
     * @param Report[] $reports
     */
    protected function groupReportsByInternalPrice(array $reports, float $basePrice): array
    {
        $groupedReports = [];
        foreach ($reports as $report) {
            $internalPrice = $this->getInternalPrice($report, $basePrice);
            $groupedReports[$internalPrice][] = $report;
        }
        return $groupedReports;
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

    /**
     * Gibt nur Reports zurück, die ein Amount Type Duration haben.
     * Das sind Reports, die auf Stundenbasis sind.
     *
     * @param Report[] $reports
     * @return Report[]
     */
    protected function filterReportsByDuration(array $reports): array
    {
        $filteredReports = [];

        foreach ($reports as $report) {
            if ($report->amount->value instanceof Duration) {
                $filteredReports[] = $report;
            }
        }

        return $filteredReports;
    }

    /**
     * Gibt nur Reports zurück die ein Amount Type Quantity haben.
     * Das sind Reports, die auf Stückbasis sind.
     *
     * @param Report[] $reports
     * @return Report[]
     */
    protected function filterReportsByQuantity(array $reports): array
    {
        $filteredReports = [];

        foreach ($reports as $report) {
            if ($report->amount->value instanceof Quantity) {
                $filteredReports[] = $report;
            }
        }

        return $filteredReports;
    }


    /**
     * @param Report[] $reports
     * @return Report[]
     */
    protected function filterReportsByBillable(array $reports): array
    {
        $filteredReports = [];

        foreach ($reports as $report) {
            if ($report->type === Report::TYPE_BILLABLE) {
                $filteredReports[] = $report;
            }
        }

        return $filteredReports;
    }

    /**
     * @param Report[] $reports
     * @return Report[]
     */
    protected function filterReportsByUnbillable(array $reports): array
    {
        $filteredReports = [];

        foreach ($reports as $report) {
            if ($report->type === Report::TYPE_UNBILLABLE) {
                $filteredReports[] = $report;
            }
        }

        return $filteredReports;
    }

    protected function stepRoundMinutes(float $minutes, float $step): float
    {
        $roundedMinutes = ceil($minutes / $step) * $step;
        return $roundedMinutes;
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

    /**
     * Addiert die Dauration Werte der Reports zusammen und liefert
     * die Summe aller Minuten (nicht gerundet und gerundet), den
     * externen und den internen Preis.
     *
     * @param Report[] $reports
     *
     * @return DurationReportCondensate
     */
    protected function condenseDurationReports(
        array $reports,
        float $baseExternalPrice,
        float $baseInternalPrice
    ): DurationReportCondensate {
        $minutes = 0;

        foreach ($reports as $report) {
            $minutes += $report->amount->value->minutes;
        }

        $hours = $minutes / 60;
        $hoursRounded = $this->stepRoundMinutes($minutes, 15) / 60;

        return new DurationReportCondensate(
            $reports,
            $hours,
            $hoursRounded,
            $this->getExternalPrice($report, $baseExternalPrice),
            $this->getInternalPrice($report, $baseInternalPrice),
            $this->getExternalPrice($report, $baseExternalPrice) * $hours,
            $this->getExternalPrice($report, $baseExternalPrice) * $hoursRounded,
            $this->getInternalPrice($report, $baseInternalPrice) * $hours,
            $this->getInternalPrice($report, $baseInternalPrice) * $hoursRounded
        );
    }

    /**
     * @param Report[] $reports
     */
    protected function condenseReports(
        array $reports,
        float $baseExternalPrice,
        float $baseInternalPrice
    ): array {
        $totalInternalPrice = 0;
        $totalExternalPrice = 0;

        foreach ($reports as $report) {
            if ($report->amount->value instanceof Duration) {
                $minutes = $report->amount->value->minutes;
                $hours = $minutes / 60;
                $hoursRounded = $this->stepRoundMinutes($minutes, 15) / 60;

                $totalInternalPrice += $hours * $this->getInternalPrice($report, $baseInternalPrice);
                $totalExternalPrice += $hoursRounded * $this->getExternalPrice($report, $baseExternalPrice);
            } elseif ($report->amount->value instanceof Quantity) {
                $quantity = $report->amount->value->value;
                $totalInternalPrice += $quantity * $report->internalPrice->value;
                $totalExternalPrice += $quantity * $report->externalPrice->value;
            }
        }

        return [
            'totalInternalPrice' => $totalInternalPrice,
            'totalExternalPrice' => $totalExternalPrice
        ];
    }
}

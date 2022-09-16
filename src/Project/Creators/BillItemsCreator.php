<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use DateTime;
use RobinTheHood\TextProjectManager\Project\Entities\Description;
use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Entities\Project;
use RobinTheHood\TextProjectManager\Project\Entities\Quantity;
use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

class BillItemsNewCreator
{
    private const PRICE_BASE_EXTERNAL = 80.00;
    private const PRICE_BASE_INTERNAL = 60.00;

    public function create(Project $project): string
    {
        $string = "####### RECHNUNGSPOSITIONEN #######\n";
        foreach ($project->tasks as $task) {
            $string .= $this->evalTask($task);
        }
        return $string;
    }

    private function evalTask(Task $task): string
    {
        $string = $this->evelBaseTask($task);

        foreach ($task->childTasks as $childTask) {
            $string .= $this->evalTask($childTask);
        }

        return $string;
    }


    private function evelBaseTask(Task $task)
    {
        $reports = [];
        foreach ($task->users as $user) {
            $reports = array_merge($reports, $user->repors);
        }
        $reports = $this->filterReportsByBillable($reports);

        if (!$reports) {
            return '';
        }

        $firstReport = $this->getFirstReportByDate($reports);
        $lastReport = $this->getLastReportByDate($reports);
        $dateRange = "{$firstReport->date} - {$lastReport->date}";
        if ($this->compareDate($firstReport->date, $lastReport->date) === 0) {
            $dateRange = "{$firstReport->date}";
        }

        $string = "{$task->name} ({$dateRange})\n";
        if ($task->description && $task->description->type === Description::TYPE_VISABLE) {
            $string .= $task->description->value . "\n";
        }
        $string .= $this->processDurationReports($reports);
        $string .= $this->processQuantityReports($reports);
        $string .= "\n";

        return $string;
    }


    private function processDurationReports(array $reports)
    {
        $durationReports = $this->filterReportsByDuration($reports);

        $groupedDurationReportsByExternalPrice = $this->groupReportsByExternalPrice($durationReports);
        $condensates = [];
        foreach ($groupedDurationReportsByExternalPrice as $reports) {
            $condensates[] = $this->condenseDurationReports($reports);
        }

        $string = '';
        foreach ($condensates as $condensate) {
            $string .= "{$condensate['hoursRounded']} Std. á {$this->formatCurrency($condensate['externalPrice'])} = {$this->formatCurrency($condensate['totalExternalPriceRounded'])}\n";
        }

        return $string;
    }

    private function processQuantityReports(array $reports): string
    {
        $quantityReports = $this->filterReportsByQuantity($reports);
        $condensate = $this->condenseQuantityReports($quantityReports);
        $string = '';
        if ($condensate['totalExternalPrice']) {
            $string = "{$this->formatCurrency($condensate['totalExternalPrice'])}\n";
        }
        return $string;
    }

    private function condenseQuantityReports(array $reports): array
    {
        $totalExternalPrice = 0;
        $totalInternalPrice = 0;
        foreach ($reports as $report) {
            $qunatity = $report->amount->value->value;
            $totalExternalPrice += $this->getExternalPrice($report) * $qunatity;
            $totalInternalPrice += $this->getInternalPrice($report) * $qunatity;
        }

        return [
            'totalExternalPrice' => $totalExternalPrice,
            'totalInternalPrice' => $totalInternalPrice
        ];
    }

    /**
     * Addiert die Dauration Werte der Reports zusammen und liefert
     * die Summe aller Minuten (nicht gerundet und gerundet), den
     * externen und den internen Preis. Alles Reports müssen den
     * gleichen external Preis haben.
     *
     * @param Report[] $reports
     */
    private function condenseDurationReports(array $reports): array
    {
        $minutes = 0;

        foreach ($reports as $report) {
            $minutes += $report->amount->value->minutes;
        }

        $hours = $minutes / 60;
        $hoursRounded = $this->stepRoundMinutes($minutes, 15) / 60;

        $condensate = [
            'hours' => $hours,
            'hoursRounded' => $hoursRounded,
            'externalPrice' => $this->getExternalPrice($report),
            'internalPrice' => $this->getInternalPrice($report),
            'totalExternalPrice' => $this->getExternalPrice($report) * $hours,
            'totalExternalPriceRounded' => $this->getExternalPrice($report) * $hoursRounded,
            'totalInternalPrice' => $this->getInternalPrice($report) * $hours,
            'totalInternalPriceRounded' => $this->getInternalPrice($report) * $hoursRounded
        ];

        return $condensate;
    }

    /**
     * Lierfert den externel Preis eines Reports ode den standard externen Preis.
     */
    private function getExternalPrice(?Report $report): float
    {
        if (!$report) {
            return self::PRICE_BASE_EXTERNAL;
        }

        if (!$report->externalPrice) {
            return self::PRICE_BASE_EXTERNAL;
        }

        return $report->externalPrice->value;
    }

    /**
     * Liefert den internen Preis eines Reports oder den standard internen Preis.
     */
    private function getInternalPrice(?Report $report): float
    {
        if (!$report) {
            return self::PRICE_BASE_INTERNAL;
        }

        if (!$report->internalPrice) {
            return self::PRICE_BASE_INTERNAL;
        }

        return $report->internalPrice->value;
    }


    /**
     * Groupiert Reports anhand ihres externen Preises.
     *
     * @param Report[] $reports
     */
    private function groupReportsByExternalPrice(array $reports): array
    {
        $groupedReports = [];
        foreach ($reports as $report) {
            $externalPrice = $this->getExternalPrice($report);
            $groupedReports[$externalPrice][] = $report;
        }
        return $groupedReports;
    }

    /**
     * Gibt nur Reports zurück die ein Amount Type Duration haben.
     * Also Reports die auf Stundenbasis sind.
     *
     * @param Report[] $reports
     * @return Report[]
     */
    private function filterReportsByDuration(array $reports): array
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
     * Also Reports die auf Stückbasis sind.
     *
     * @param Report[] $reports
     * @return Report[]
     */
    private function filterReportsByQuantity(array $reports): array
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
    private function filterReportsByBillable(array $reports): array
    {
        $filteredReports = [];

        foreach ($reports as $report) {
            if ($report->type === Report::TYPE_BILLABLE) {
                $filteredReports[] = $report;
            }
        }

        return $filteredReports;
    }

    private function stepRoundMinutes(float $minutes, float $step): float
    {
        $roundedMinutes = ceil($minutes / $step) * $step;
        return $roundedMinutes;
    }

    private function formatCurrency(float $value): string
    {
        return number_format($value, 2, ',', '.') . '€';
    }

    private function getFirstReportByDate(array $reports): Report
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

    private function getLastReportByDate(array $reports): Report
    {
        $lastReport = null;
        foreach ($reports as $report) {
            if (!$lastReport || $this->compareDate($lastReport->date, $report->date) < 0) {
                $lastReport = $report;
            }
        }
        return $lastReport;
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
}

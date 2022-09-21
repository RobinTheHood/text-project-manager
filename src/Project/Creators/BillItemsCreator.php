<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Description;
use RobinTheHood\TextProjectManager\Project\Entities\Project;
use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

class BillItemsCreator extends AbstractCreator
{
    private const PRICE_BASE_EXTERNAL = 80.00;
    private const PRICE_BASE_INTERNAL = 60.00;

    public function create(Project $project): string
    {
        $string = '';
        // $string .= "####### RECHNUNGSPOSITIONEN #######\n";
        foreach ($project->tasks as $task) {
            $string .= $this->evalTask($task);
        }
        return $string;
    }

    private function evalTask(Task $task): string
    {
        $string = $this->evalBaseTask($task);

        foreach ($task->childTasks as $childTask) {
            $string .= $this->evalTask($childTask);
        }

        return $string;
    }

    private function evalBaseTask(Task $task)
    {
        $reports = $this->getAllReportsFromTask($task);
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

        $groupedDurationReportsByExternalPrice = $this->groupReportsByExternalPrice(
            $durationReports,
            self::PRICE_BASE_EXTERNAL
        );
        $condensates = [];
        foreach ($groupedDurationReportsByExternalPrice as $reports) {
            $condensates[] = $this->condenseDurationReports(
                $reports,
                self::PRICE_BASE_EXTERNAL,
                self::PRICE_BASE_INTERNAL
            );
        }

        $string = '';
        foreach ($condensates as $condensate) {
            $formatedHours = $this->formatHours($condensate['hoursRounded']);
            $formatedExternalPrice = $this->formatCurrency($condensate['externalPrice']);
            $formatedTotalExternalPrice = $this->formatCurrency($condensate['totalExternalPriceRounded']);
            $string .= "$formatedHours á $formatedExternalPrice = $formatedTotalExternalPrice\n";
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
            $totalExternalPrice += $this->getExternalPrice($report, self::PRICE_BASE_EXTERNAL) * $qunatity;
            $totalInternalPrice += $this->getInternalPrice($report, self::PRICE_BASE_INTERNAL) * $qunatity;
        }

        return [
            'totalExternalPrice' => $totalExternalPrice,
            'totalInternalPrice' => $totalInternalPrice
        ];
    }
}

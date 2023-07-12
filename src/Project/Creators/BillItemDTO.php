<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

class BillItemDTO
{
    /** @var Task*/
    private $task;

    /** @var Report*/
    private $firstReport;

    /** @var Report*/
    private $lastReport;

    /** @var float*/
    private $targetHours;

    /** @var float*/
    private $targetTotalPrice;

    /** @var ReportCondensate[] */
    private $durationReportCondensates;

    /** @var ReportCondensate[] */
    private $quantityReportCondensates;

    /**
     * @param DurationReportCondensate[] $durationReportCondensates
     */
    public function __construct(
        Task $task,
        Report $firstReport,
        Report $lastReport,
        float $targetHours,
        float $targetTotalPrice,
        array $durationReportCondensates,
        array $quantityReportCondensates
    ) {
        $this->task = $task;
        $this->firstReport = $firstReport;
        $this->lastReport = $lastReport;
        $this->targetHours = $targetHours;
        $this->targetTotalPrice = $targetTotalPrice;
        $this->durationReportCondensates = $durationReportCondensates;
        $this->quantityReportCondensates = $quantityReportCondensates;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function getFirstReport(): Report
    {
        return $this->firstReport;
    }

    public function getLastReport(): Report
    {
        return $this->lastReport;
    }

    public function getTargetHours(): float
    {
        return $this->targetHours;
    }

    public function getTargetTotalPrice(): float
    {
        return $this->targetTotalPrice;
    }

    /**
     * @return ReportCondensate[]
     */
    public function getDurationReportCondensates(): array
    {
        return $this->durationReportCondensates;
    }

    /**
     * @return ReportCondensate[]
     */
    public function getQuantityReportCondensates(): array
    {
        return $this->quantityReportCondensates;
    }

    public function getTotalPrice(): float
    {
        $task = $this->getTask();

        if ($task->target) {
            return $this->getTargetTotalPrice();
        } else {
            $total = 0;

            foreach ($this->getDurationReportCondensates() as $reportCondensate) {
                $total += $reportCondensate->getExternalTotalPrice();
            }

            foreach ($this->getQuantityReportCondensates() as $reportCondensate) {
                $total += $reportCondensate->getExternalTotalPrice();
            }

            return $total;
        }
    }
}

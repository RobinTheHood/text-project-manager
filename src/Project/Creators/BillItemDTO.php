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
    private $totalPrice;

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
        float $totalPrice,
        array $durationReportCondensates,
        array $quantityReportCondensates
    ) {
        $this->task = $task;
        $this->firstReport = $firstReport;
        $this->lastReport = $lastReport;
        $this->targetHours = $targetHours;
        $this->totalPrice = $totalPrice;
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

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
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
}

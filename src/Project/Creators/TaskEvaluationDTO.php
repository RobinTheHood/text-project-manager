<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

/**
 * DTO - Data transfer Object
 */
class TaskEvaluationDTO
{
    private Task $task;
    private Report $firstReport;
    private Report $lastReport;
    private array $actual;
    private array $target;
    private float $targetPrice;
    private float $targetPriceMin;
    private float $targetPriceMax;
    private float $contributionMargin;

    public function __construct(
        Task $task,
        Report $firstReport,
        Report $lastReport,
        array $actual,
        array $target,
        float $targetPrice,
        float $targetPriceMin,
        float $targetPriceMax,
        float $contributionMargin
    ) {
        $this->task = $task;
        $this->firstReport = $firstReport;
        $this->lastReport = $lastReport;
        $this->actual = $actual;
        $this->target = $target;
        $this->targetPrice = $targetPrice;
        $this->targetPriceMin = $targetPriceMin;
        $this->targetPriceMax = $targetPriceMax;
        $this->contributionMargin = $contributionMargin;
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

    public function getActual(): array
    {
        return $this->actual;
    }

    public function getTarget(): array
    {
        return $this->target;
    }

    public function getTargetPrice(): float
    {
        return $this->targetPrice;
    }

    public function getTargetPriceMin(): float
    {
        return $this->targetPriceMin;
    }

    public function getTargetPriceMax(): float
    {
        return $this->targetPriceMax;
    }

    public function getContributionMargin(): float
    {
        return $this->contributionMargin;
    }
}

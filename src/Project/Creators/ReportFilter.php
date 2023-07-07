<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Entities\Quantity;
use RobinTheHood\TextProjectManager\Project\Entities\Report;

class ReportFilter
{
    /** @var ReportPriceSelector */
    private $reportPriceSelector;

    public function __construct(ReportPriceSelector $reportPriceSelector)
    {
        $this->reportPriceSelector = $reportPriceSelector;
    }

    /**
     * Gibt nur Reports zurück, die ein Amount Type Duration haben.
     * Das sind Reports, die auf Stundenbasis sind.
     *
     * @param Report[] $reports
     * @return Report[]
     */
    public function filterByDuration(array $reports): array
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
    public function filterByQuantity(array $reports): array
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
    public function filterByBillable(array $reports): array
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
    public function filterByUnbillable(array $reports): array
    {
        $filteredReports = [];

        foreach ($reports as $report) {
            if ($report->type === Report::TYPE_UNBILLABLE) {
                $filteredReports[] = $report;
            }
        }

        return $filteredReports;
    }

    /**
     * @param Report[] $reports
     * @return Report[]
     */
    public function filterByExternalPrice(array $reports, float $selectedPrice, float $defaultPrice)
    {
        $filteredReports = [];

        foreach ($reports as $report) {
            $exteranlPrice = $this->reportPriceSelector->getExternal($report, $defaultPrice);
            if ($exteranlPrice === $selectedPrice) {
                $filteredReports[] = $report;
            }
        }

        return $filteredReports;
    }
}

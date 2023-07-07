<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Report;

class ReportGrouper
{
    /** @var ReportPriceSelector */
    private $reportPriceSelector;

    public function __construct(ReportPriceSelector $reportPriceSelector)
    {
        $this->reportPriceSelector = $reportPriceSelector;
    }

    /**
     * Gruppiert Reports anhand ihres externen Preises.
     *
     * @param Report[] $reports
     * @param float $basePrice - Der externe Preis, falls ein Report keinen externen Preis hinterlegt hat
     */
    public function groupByExternalPrice(array $reports, float $basePrice): array
    {
        $groupedReports = [];
        foreach ($reports as $report) {
            $externalPrice = $this->reportPriceSelector->getExternal($report, $basePrice);
            $groupedReports[$externalPrice][] = $report;
        }
        return $groupedReports;
    }

    /**
     * Gruppiert Reports anhand ihres internen Preises.
     *
     * @param Report[] $reports
     * @param float $basePrice - Der interne Preis, falls ein Report keinen internen Preis hinterlegt hat
     */
    public function groupByInternalPrice(array $reports, float $basePrice): array
    {
        $groupedReports = [];
        foreach ($reports as $report) {
            $internalPrice = $this->reportPriceSelector->getInternal($report, $basePrice);
            $groupedReports[$internalPrice][] = $report;
        }
        return $groupedReports;
    }
}

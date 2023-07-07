<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Entities\Quantity;
use RobinTheHood\TextProjectManager\Project\Entities\Report;

class ReportCondensateFactory
{
    /** @var ReportPriceSelector */
    private $reportPriceSelector;

    public function __construct(ReportPriceSelector $reportPriceSelector)
    {
        $this->reportPriceSelector = $reportPriceSelector;
    }

    /**
     * @param Report[] $reports
     *
     * @return ReportCondensate[]
     */
    public function createFromReports(array $reports, float $defaultExternalPrice, float $defaultInternalPrice): array
    {
        $reportCondensates = [];
        foreach ($reports as $report) {
            $reportCondensates[] = $this->createFromReport($report, $defaultExternalPrice, $defaultInternalPrice);
        }
        return $reportCondensates;
    }

    public function createFromReport(
        Report $report,
        float $defaultExternalPrice,
        float $defaultInternalPrice
    ): ReportCondensate {
        if ($report->amount->value instanceof Duration) {
            $type = ReportCondensate::TYPE_DURATION;
            $minutes = $report->amount->value->minutes;
            $internalQuantity = $minutes / 60;
            $externalQuantity = $this->stepRoundMinutes($minutes, 15) / 60;
        } elseif ($report->amount->value instanceof Quantity) {
            $type = ReportCondensate::TYPE_QUANTITY;
            $internalQuantity = $report->amount->value->value;
            $externalQuantity = $report->amount->value->value;
        }

        $reports = [$report];
        $externalPrice = $this->reportPriceSelector->getExternal($report, $defaultExternalPrice);
        $internalPrice = $this->reportPriceSelector->getInternal($report, $defaultInternalPrice);
        $description = $report->description;

        return new ReportCondensate(
            $type,
            $reports,
            $externalQuantity,
            $internalQuantity,
            $externalPrice,
            $internalPrice,
            $description
        );
    }

    protected function stepRoundMinutes(float $minutes, float $step): float
    {
        $roundedMinutes = ceil($minutes / $step) * $step;
        return $roundedMinutes;
    }
}

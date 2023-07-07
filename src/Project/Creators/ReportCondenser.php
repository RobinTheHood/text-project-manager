<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Entities\Quantity;
use RobinTheHood\TextProjectManager\Project\Entities\Report;

class ReportCondenser
{
    /** @var ReportGrouper */
    private $reportGrouper;

    /** @var ReportFilter */
    private $reportFilter;

    /** @var ReportPriceSelector */
    private $reportPriceSelector;

    public function __construct(
        ReportGrouper $reportGrouper,
        ReportFilter $reportFilter,
        ReportPriceSelector $reportPriceSelector
    ) {
        $this->reportGrouper = $reportGrouper;
        $this->reportFilter = $reportFilter;
        $this->reportPriceSelector = $reportPriceSelector;
    }


    /**
     * Kondensiert alle Reports auf einen externen Preis
     *   4h a 80€
     * + 5h a 120€
     * + 2x 120€
     * + 4x 140€
     *
     * @param Report[] $reports
     */
    public function toTotalExternal(array $reports, float $baseExternalPrice): float
    {
        $durationTotalExternal = $this->durationToTotalExternal($reports, $baseExternalPrice);
        $quantityTotalExternal = $this->quantityToTotalExternal($reports);

        return $durationTotalExternal + $quantityTotalExternal;
    }


    /**
     * Kondensiert Duration-Reports auf einen externen Preis
     *   4h a 80€
     * + 5h a 120€
     * = 920€
     *
     * @param Report[] $reports
     */
    public function durationToTotalExternal(array $reports, float $baseExternalPrice): float
    {
        $reports = $this->reportFilter->filterByDuration($reports);

        $totalExternalPrice = 0;

        foreach ($reports as $report) {
            $minutes = $report->amount->value->minutes;
            $hoursRounded = $this->stepRoundMinutes($minutes, 15) / 60;

            $totalExternalPrice += $hoursRounded * $this->reportPriceSelector->getExternal($report, $baseExternalPrice);
        }

        return $totalExternalPrice;
    }

    /**
     * Kondensiert Duration-Reports auf eine Zeit
     *   4h a 80€
     * + 5h a 120€
     * = 9h
     *
     * @param Report[] $reports
     */
    public function durationToDuration(array $reports): array
    {
        $reports = $this->reportFilter->filterByDuration($reports);

        $totalHours = 0;
        $totalHoursRounded = 0;

        foreach ($reports as $report) {
            $minutes = $report->amount->value->minutes;
            $hours = $minutes / 60;
            $hoursRounded = $this->stepRoundMinutes($minutes, 15) / 60;

            $totalHours += $hours;
            $totalHoursRounded += $hoursRounded;
        }

        return [
            'totalHours' => $totalHours,
            'totalHoursRounded' => $totalHoursRounded
        ];
    }

    /**
     * Kondensiert Duration-Reports mit gleichem externen Price auf auf eine externen Preis
     * Wenn 80€ der ausgewählte externe Preis ist
     *   4h a 80€
     * + 2h a 80€
     * + 5h a 120€
     * = 6h a 80€ = 480
     *
     * @param Report[] $reports
     */
    public function durationByExternalPrice(
        array $reports,
        float $selectedPrice,
        float $defaultPrice
    ): array {
        $reports = $this->reportFilter->filterByDuration($reports);
        $reports = $this->reportFilter->filterByExternalPrice($reports, $selectedPrice, $defaultPrice);

        if (!$reports) {
            return [];
        }

        $condensate = $this->durationToDuration($reports);

        return [
            'totalHoursRounded' => $condensate['totalHoursRounded'],
            'externalPrice' => $selectedPrice,
            'totalExternalPrice' => $condensate['totalHoursRounded'] * $selectedPrice
        ];
    }

    /**
     * Kondensiert Quantity-Reports auf einen externen Preis
     *   2x 120€
     * + 4x 140€
     * = 800 €
     *
     * @param Report[] $reports
     */
    public function quantityToTotalExternal(array $reports): float
    {
        $reports = $this->reportFilter->filterByQuantity($reports);

        $totalExternalPrice = 0;

        foreach ($reports as $report) {
            $quantity = $report->amount->value->value;
            $totalExternalPrice += $quantity * $report->externalPrice->value;
        }

        return $totalExternalPrice;
    }

    private function stepRoundMinutes(float $minutes, float $step): float
    {
        $roundedMinutes = ceil($minutes / $step) * $step;
        return $roundedMinutes;
    }
}

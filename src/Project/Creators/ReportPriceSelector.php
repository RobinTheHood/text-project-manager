<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Report;

class ReportPriceSelector
{
    /**
     * Lierfert den externel Preis eines Reports oder den standard externen Preis, falls ein Report keinen
     * externen Price festgelegt hat.
     */
    public function getExternal(?Report $report, float $basePrice): float
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
     * Liefert den internen Preis eines Reports oder den standard internen Preis, , falls ein Report keinen
     * internen Price festgelegt hat.
     */
    public function getInternal(?Report $report, float $basePrice): float
    {
        if (!$report) {
            return $basePrice;
        }

        if (!$report->internalPrice) {
            return $basePrice;
        }

        return $report->internalPrice->value;
    }
}

<?php

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Report;

class QuantityReportCondensate
{
    /** @var Report[] */
    private $reports;

    /** @var float */
    private $totalExternalPrice;

    /** @var float */
    private $totalInternalPrice;

    /**
     * @param Report[] $reports
     * @param float $totalExternalPrice
     * @param float $totalInternalPrice
     */
    public function __construct(
        array $reports,
        float $totalExternalPrice,
        float $totalInternalPrice
    ) {
        $this->reports = $reports;
        $this->totalExternalPrice = $totalExternalPrice;
        $this->totalInternalPrice = $totalInternalPrice;
    }

    /**
     * @return Report[]
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    public function getTotalExternalPrice(): float
    {
        return $this->totalExternalPrice;
    }


    public function getTotalInternalPrice(): float
    {
        return $this->totalInternalPrice;
    }
}

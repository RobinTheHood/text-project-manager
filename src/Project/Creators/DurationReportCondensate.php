<?php

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Report;

class DurationReportCondensate
{
    /** @var Report[] */
    private array $reports;

    /** @var float */
    private $hours;

    /** @var float */
    private $hoursRounded;

    /** @var float */
    private $externalPrice;

    /** @var float */
    private $internalPrice;

    /** @var float */
    private $totalExternalPrice;

    /** @var float */
    private $totalExternalPriceRounded;

    /** @var float */
    private $totalInternalPrice;

    /**
     * @param Report[] $reports
     * @param float $hours
     * @param float $hoursRounded
     * @param float $externalPrice
     * @param float $internalPrice
     * @param float $totalExternalPrice
     * @param float $totalExternalPriceRounded
     * @param float $totalInternalPrice
     */
    public function __construct(
        array $reports,
        float $hours,
        float $hoursRounded,
        float $externalPrice,
        float $internalPrice,
        float $totalExternalPrice,
        float $totalExternalPriceRounded,
        float $totalInternalPrice
    ) {
        $this->reports = $reports;
        $this->hours = $hours;
        $this->hoursRounded = $hoursRounded;
        $this->externalPrice = $externalPrice;
        $this->internalPrice = $internalPrice;
        $this->totalExternalPrice = $totalExternalPrice;
        $this->totalExternalPriceRounded = $totalExternalPriceRounded;
        $this->totalInternalPrice = $totalInternalPrice;
    }

    /**
     * @return Report[]
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    public function getHours(): float
    {
        return $this->hours;
    }

    public function getHoursRounded(): float
    {
        return $this->hoursRounded;
    }

    public function getExternalPrice(): float
    {
        return $this->externalPrice;
    }

    public function getInternalPrice(): float
    {
        return $this->internalPrice;
    }

    public function getTotalExternalPrice(): float
    {
        return $this->totalExternalPrice;
    }

    public function getTotalExternalPriceRounded(): float
    {
        return $this->totalExternalPriceRounded;
    }

    public function getTotalInternalPrice(): float
    {
        return $this->totalInternalPrice;
    }
}

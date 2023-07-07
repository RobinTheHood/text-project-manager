<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use InvalidArgumentException;

class ReportCondensate
{
    public const TYPE_DURATION = 'duration';
    public const TYPE_QUANTITY = 'quantity';

    /** @var string */
    private $type;

    /** @var Report[] */
    private $reports;

    /** @var float */
    private $externalQuantity;

    /** @var float */
    private $internalQuantity;

    /** @var float */
    private $externalPrice;

    /** @var float */
    private $internalPrice;

    /** @var string */
    private $description;

    /**
     * @param string $type
     * @param Report[] $reports
     * @param float $externalQuantity
     * @param float $internalQuantity
     * @param float $externalPrice
     * @param float $internalPrice
     * @param string $description
     */
    public function __construct(
        string $type,
        array $reports,
        float $externalQuantity,
        float $internalQuantity,
        float $externalPrice,
        float $internalPrice,
        string $description
    ) {
        if (!$this->isAllowedTypes($type)) {
            throw new InvalidArgumentException("Type $type is not a valid type");
        }

        $this->type = $type;
        $this->reports = $reports;
        $this->externalQuantity = $externalQuantity;
        $this->internalQuantity = $internalQuantity;
        $this->externalPrice = $externalPrice;
        $this->internalPrice = $internalPrice;
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Report[]
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    public function getExternalQuantity(): float
    {
        return $this->externalQuantity;
    }

    public function getInternalQuantity(): float
    {
        return $this->internalQuantity;
    }

    public function getExternalPrice(): float
    {
        return $this->externalPrice;
    }

    public function getExternalTotalPrice(): float
    {
        return $this->externalPrice * $this->externalQuantity;
    }

    public function getInternalPrice(): float
    {
        return $this->internalPrice;
    }

    public function getInternalTotalPrice(): float
    {
        return $this->internalPrice * $this->internalQuantity;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    private function isAllowedTypes(string $type): bool
    {
        if ($type === self::TYPE_DURATION) {
            return true;
        }

        if ($type === self::TYPE_QUANTITY) {
            return true;
        }

        return false;
    }
}

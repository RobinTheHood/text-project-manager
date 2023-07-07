<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use InvalidArgumentException;

class ReportCondensateAdder
{
    /**
     * @param ReportCondensate[] $condensates;
     */
    public function addAll(array $condensates): ReportCondensate
    {
        if (!$condensates) {
            throw new InvalidArgumentException('Empty array of ReportCondensates is not allowed');
        }

        $resultCondensate = null;
        foreach ($condensates as $condensate) {
            if (!$resultCondensate) {
                $resultCondensate = $condensate;
                continue;
            }

            $resultCondensate = $this->add($resultCondensate, $condensate);
        }

        return $resultCondensate;
    }

    public function add(ReportCondensate $condensate1, ReportCondensate $condensate2): ReportCondensate
    {
        $typ1 = $condensate1->getType();
        $typ2 = $condensate2->getType();

        if ($typ1 === ReportCondensate::TYPE_DURATION && $typ2 === ReportCondensate::TYPE_DURATION) {
            return $this->addDurationCondensates($condensate1, $condensate2);
        }

        if ($typ1 === ReportCondensate::TYPE_QUANTITY && $typ2 === ReportCondensate::TYPE_QUANTITY) {
            return $this->addQuantityCondensates($condensate1, $condensate2);
        }

        return $this->addMixedCondensates($condensate1, $condensate2);
    }

    /**
     *   4h  4,1h    A   80€ 20€ Duration
     * + 2h  2,1h    A   80€ 20€ Duration
     * = 6h  6,2h    A   80€ 20€ Duration
     *
     *   4h  4,1h    A   80€ 20€ Duration
     * + 2h  2,1h    B   80€ 20€ Duration
     * = 6h  6,2h    A,B 80€ 20€ Duration
     *
     *   4h  4,5h    A   80€ 20€ Duration
     * + 2h  1,5h    B   20€  8€ Duration
     * = 6h  6,0h    A,B 60€ 17€ Duration
     */
    private function addDurationCondensates(
        ReportCondensate $condensate1,
        ReportCondensate $condensate2
    ): ReportCondensate {
        $internalQuantity = $condensate1->getInternalQuantity() + $condensate2->getInternalQuantity();
        $externalQuantity = $condensate1->getExternalQuantity() + $condensate2->getExternalQuantity();

        $externalTotalPrice = $condensate1->getExternalTotalPrice() + $condensate2->getExternalTotalPrice();
        $externalPrice = $externalTotalPrice / $externalQuantity;

        $internalTotalPrice = $condensate1->getInternalTotalPrice() + $condensate2->getInternalTotalPrice();
        $internalPrice = $internalTotalPrice / $internalQuantity;

        $description = $this->addDescription($condensate1, $condensate2);
        $reports = $this->addReports($condensate1, $condensate2);

        return new ReportCondensate(
            ReportCondensate::TYPE_DURATION,
            $reports,
            $externalQuantity,
            $internalQuantity,
            $externalPrice,
            $internalPrice,
            $description
        );
    }

    /**
     *   1x  1x A   80€ 20€ Quantity
     * + 2x  2x A   80€ 20€ Quantity
     * = 3x  3x A   80€ 20€ Quantity
     *
     *   1x  1x A    80€ 20€ Quantity
     * + 2x  2x B    80€ 20€ Quantity
     * = 1x  1x A,B 240€ 60€ Quantity
     */
    private function addQuantityCondensates(
        ReportCondensate $condensate1,
        ReportCondensate $condensate2
    ): ReportCondensate {

        if ($this->isEqualQuantityCondensate($condensate1, $condensate2)) {
            $externalQuantity = $condensate1->getExternalQuantity() + $condensate2->getExternalQuantity();
            $internalQuantity = $condensate1->getInternalQuantity() + $condensate2->getInternalQuantity();
            $externalPrice = $condensate1->getExternalPrice();
            $internalPrice = $condensate1->getInternalPrice();
        } else {
            $externalQuantity = 1;
            $internalQuantity = 1;
            $externalPrice = $condensate1->getExternalTotalPrice() + $condensate2->getExternalTotalPrice();
            $internalPrice = $condensate1->getInternalTotalPrice() + $condensate2->getInternalTotalPrice();
        }

        $description = $this->addDescription($condensate1, $condensate2);
        $reports = $this->addReports($condensate1, $condensate2);

        return new ReportCondensate(
            ReportCondensate::TYPE_QUANTITY,
            $reports,
            $externalQuantity,
            $internalQuantity,
            $externalPrice,
            $internalPrice,
            $description
        );
    }

    /**
     *   4h A    80€  20€ Duration
     * + 2x B    40€  10€ Quantity
     * = 1x A,B 400€ 100€ Quantity
     */
    private function addMixedCondensates(
        ReportCondensate $condensate1,
        ReportCondensate $condensate2
    ): ReportCondensate {
        $externalQuantity = 1;
        $internalQuantity = 1;
        $externalPrice = $condensate1->getExternalTotalPrice() + $condensate2->getExternalTotalPrice();
        $internalPrice = $condensate1->getInternalTotalPrice() + $condensate2->getInternalTotalPrice();

        $description = $this->addDescription($condensate1, $condensate2);
        $reports = $this->addReports($condensate1, $condensate2);

        return new ReportCondensate(
            ReportCondensate::TYPE_QUANTITY,
            $reports,
            $externalQuantity,
            $internalQuantity,
            $externalPrice,
            $internalPrice,
            $description
        );
    }

    /**
     * @return Report[]
     */
    private function addReports(ReportCondensate $condensate1, ReportCondensate $condensate2): array
    {
        return array_merge($condensate1->getReports(), $condensate2->getReports());
    }

    private function addDescription(ReportCondensate $condensate1, ReportCondensate $condensate2)
    {
        if ($condensate1->getDescription() === $condensate2->getDescription()) {
            return $condensate1->getDescription();
        }

        $description = implode(', ', [$condensate1->getDescription(), $condensate2->getDescription()]);
        return $description;
    }

    /**
     * Überprüft, ob es sich um die gleichen ReportCondenstes vom Type Quantity handelt. Es müssen
     * alle Felder gleich sein BIS AUF quantity
     */
    public function isEqualQuantityCondensate(ReportCondensate $condensate1, ReportCondensate $condensate2): bool
    {
        if ($condensate1->getType() !== ReportCondensate::TYPE_QUANTITY) {
            return false;
        }

        if ($condensate2->getType() !== ReportCondensate::TYPE_QUANTITY) {
            return false;
        }

        if ($condensate1->getExternalQuantity() !== $condensate1->getInternalQuantity()) {
            return false;
        }

        if ($condensate2->getExternalQuantity() !== $condensate2->getInternalQuantity()) {
            return false;
        }

        if ($condensate1->getDescription() !== $condensate2->getDescription()) {
            return false;
        }

        if ($condensate1->getExternalPrice() !== $condensate2->getExternalPrice()) {
            return false;
        }

        if ($condensate1->getInternalPrice() !== $condensate2->getInternalPrice()) {
            return false;
        }

        return true;
    }
}

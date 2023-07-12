<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use DateTime;

abstract class AbstractView
{
    protected function formatCurrency(float $value): string
    {
        return number_format($value, 2, ',', '.') . ' €';
    }

    protected function formatHours(float $value): string
    {
        return str_replace('.', ',', '' . $value) . ' Std.';
    }

    protected function compareDate(string $germanDate1, string $germanDate2): int
    {
        $dateTime1 = new DateTime($germanDate1);
        $dateTime2 = new DateTime($germanDate2);

        if ($dateTime1->getTimestamp() < $dateTime2->getTimestamp()) {
            return -1;
        } elseif ($dateTime1->getTimestamp() > $dateTime2->getTimestamp()) {
            return 1;
        }

        return 0;
    }
}

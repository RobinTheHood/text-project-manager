<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

use App\Project\Entities\MoneyRange;

interface MoneyRangeParserInterface
{
    public function parse(string $string): MoneyRange;
}

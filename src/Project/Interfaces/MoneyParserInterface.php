<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

use App\Project\Entities\Money;

interface MoneyParserInterface
{
    public function parse(string $string): Money;
}

<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

use App\Project\Entities\Amount;

interface AmountParserInterface
{
    public function parse(string $string): Amount;
}

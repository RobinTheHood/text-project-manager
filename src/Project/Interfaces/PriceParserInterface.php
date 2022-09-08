<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

interface PriceParserInterface
{
    public function parse(string $string): float;
}

<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use RobinTheHood\TextProjectManager\Project\Interfaces\PriceParserInterface;

class PriceParser implements PriceParserInterface
{
    public function parse(string $string): float
    {
        $valueString = str_replace(',', '.', $string);
        return floatval($valueString);
    }
}

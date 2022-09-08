<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Interfaces\PriceParserInterface;

class PriceParser implements PriceParserInterface
{
    public function parse(string $string): float
    {
        $valueString = str_replace(',', '.', $string);
        return floatval($valueString);
    }
}

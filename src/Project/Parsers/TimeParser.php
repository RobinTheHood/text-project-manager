<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Interfaces\TimeParserInterface;
use Exception;

class TimeParser implements TimeParserInterface
{
    public function parse(string $string): array
    {
        if (strpos($string, 'min') !== false) {
            $unit = 'min';
            $value = str_replace('min', '', $string);
        } elseif (strpos($string, 'h') !== false) {
            $unit = 'h';
            $value = str_replace('h', '', $string);
        } else {
            throw new Exception("Unkown unit");
        }

        return [
            'value' => $value,
            'unit' => $unit
        ];
    }
}

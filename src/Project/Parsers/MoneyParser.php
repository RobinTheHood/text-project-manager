<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Entities\Money;
use App\Project\Interfaces\MoneyParserInterface;
use Exception;

class MoneyParser implements MoneyParserInterface
{
    public function parse(string $string): Money
    {
        if (strpos($string, '€') === false) {
            throw new Exception("Unkown Money format. Missing Currency.");
        }

        if (strpos($string, ',') === false) {
            throw new Exception("Unkown Money format. Missing decimals.");
        }

        $string = str_replace('€', '', $string);
        $string = trim($string);

        $money = new Money();
        $money->value = $string;

        return $money;
    }
}

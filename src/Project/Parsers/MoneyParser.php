<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use RobinTheHood\TextProjectManager\Project\Entities\Money;
use RobinTheHood\TextProjectManager\Project\Interfaces\MoneyParserInterface;
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

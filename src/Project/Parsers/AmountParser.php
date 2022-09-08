<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Entities\Amount;
use App\Project\Interfaces\AmountParserInterface;
use Exception;

class AmountParser implements AmountParserInterface
{
    public function parse(string $string): Amount
    {
        $amount = new Amount();

        $valueStr = '';
        $value = 0.0;
        $type = Amount::TYPE_TIME;

        if (strpos($string, 'min') !== false) {
            $valueStr = str_replace('min', '', $string);
            $value = floatval($valueStr);
            $type = Amount::TYPE_TIME;
        } elseif (strpos($string, 'h') !== false) {
            $valueStr = str_replace('h', '', $string);
            $value = floatval($valueStr) * 60;
            $type = Amount::TYPE_TIME;
        } elseif (strpos($string, 'x') !== false) {
            $valueStr = str_replace('x', '', $string);
            $value = floatval($valueStr);
            $type = Amount::TYPE_FIX;
        } else {
            throw new Exception("Unkown unit in Qunatity field");
        }

        $amount->type = $type;
        $amount->value = $value;

        return $amount;
    }
}

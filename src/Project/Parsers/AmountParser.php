<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Entities\Amount;
use App\Project\Entities\Time;
use App\Project\Interfaces\AmountParserInterface;
use App\Project\Interfaces\TimeParserInterface;
use Exception;

class AmountParser implements AmountParserInterface
{
    /**
     * @var TimeParaserInterface
     */
    private $timeParser;

    public function __construct(TimeParserInterface $timeParser)
    {
        $this->timeParser = $timeParser;
    }

    public function parse(string $string): Amount
    {
        if ($this->isTime($string)) {
            $amount = $this->parseTime($string);
            return $amount;
        } elseif ($this->isQuantity($string)) {
            $amount = $this->parseQuantity($string);
            return $amount;
        }

        throw new Exception("Can not parse Amount");
    }


    private function parseTime(string $string): Amount
    {
        $amount = new Amount();
        $amount->value = $this->timeParser->parse($string);
        $amount->type = Amount::TYPE_TIME;
        return $amount;
    }

    private function parseQuantity(string $string): Amount
    {
        $string = str_replace('x', '', $string);
        $string = str_replace('Stk', '', $string);
        $string = trim($string);

        $amount = new Amount();
        $amount->value = $string;
        $amount->type = Amount::TYPE_QUANTITY;
        return $amount;
    }

    private function isTime(string $string): bool
    {
        if (strpos($string, 'min') !== false) {
            return true;
        }

        if (strpos($string, 'h') !== false) {
            return true;
        }

        if (strpos($string, ':') !== false) {
            return true;
        }

        return false;
    }

    private function isQuantity(string $string): bool
    {
        if (strpos($string, 'x') !== false) {
            return true;
        }

        if (strpos($string, 'Stk') !== false) {
            return true;
        }

        return false;
    }
}

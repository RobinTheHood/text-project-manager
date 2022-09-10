<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Entities\Time;
use App\Project\Interfaces\TimeParserInterface;
use Exception;

class TimeParser implements TimeParserInterface
{
    public function parse(string $string): Time
    {
        if (strpos($string, 'min') !== false) {
            return $this->parseMinute($string);
        } elseif (strpos($string, 'h') !== false) {
            return $this->parseHour($string);
        } elseif (strpos($string, ':') !== false) {
            return $this->parseMixed($string);
        } else {
            throw new Exception("Unkown Timeformat");
        }
    }

    private function parseMinute(string $string): Time
    {
        $string = str_replace('min', '', $string);
        $value = $this->parseValue($string);

        $time = new Time();
        $time->type = Time::TYPE_MINUTE;
        $time->value = $value;

        return $time;
    }

    private function parseHour(string $string): Time
    {
        $string = str_replace('h', '', $string);
        $value = $this->parseValue($string);

        $time = new Time();
        $time->type = Time::TYPE_HOUR;
        $time->value = $value;

        return $time;
    }

    private function parseMixed(string $string): Time
    {
        $value = $this->parseValue($string);

        $time = new Time();
        $time->type = Time::TYPE_MIXED;
        $time->value = $value;

        return $time;
    }

    private function parseValue(string $string): string
    {
        $string = trim($string);

        $fistChar = mb_substr($string, 0, 1, 'utf-8');
        $lastChar = mb_substr($string, -1, 1, 'utf-8');

        if (!is_numeric($fistChar)) {
            throw new Exception('Unkown Timeformat');
        }

        if (!is_numeric($lastChar)) {
            throw new Exception('Unkown Timeformat');
        }

        return $string;
    }
}

<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use RobinTheHood\TextProjectManager\Project\Entities\Target;
use RobinTheHood\TextProjectManager\Project\Interfaces\TargetParserInterface;
use RobinTheHood\TextProjectManager\Project\Interfaces\TimeParserInterface;
use RobinTheHood\TextProjectManager\Project\Interfaces\TimeRangeParserInterface;
use RobinTheHood\TextProjectManager\Project\Interfaces\MoneyParserInterface;
use RobinTheHood\TextProjectManager\Project\Interfaces\MoneyRangeParserInterface;
use Exception;

class TargetParser implements TargetParserInterface
{
    /**
     * @var TimeParserInterface
     */
    private $timeParser;

    public function __construct(
        TimeParserInterface $timeParser,
        TimeRangeParserInterface $timeRangeParser,
        MoneyParserInterface $moneyParser,
        MoneyRangeParserInterface $moneyRangeParser
    ) {
        $this->timeParser = $timeParser;
        $this->timeRangeParser = $timeRangeParser;
        $this->moneyParser = $moneyParser;
        $this->moneyRangeParser = $moneyRangeParser;
    }

    public function parse(string $string): Target
    {
        $string = trim($string);

        $target = new Target();
        $target->value = null;
        $target->type = Target::TYPE_NONE;

        if (!$string) {
            return $target;
        }

        if ($this->isRange($string)) {
            $target = $this->parseRange($string);
        } else {
            $target = $this->parseSolo($string);
        }

        return $target;
    }

    private function parseSolo(string $string): Target
    {
        $target = new Target();

        $error = '';

        try {
            $target->value = $this->timeParser->parse($string);
            $target->type = Target::TYPE_TIME;
            return $target;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        try {
            $target->value = $this->moneyParser->parse($string);
            $target->type = Target::TYPE_MONEY;
            return $target;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        throw new Exception("Cant parse target. $error");
    }

    private function parseRange($string): Target
    {
        $target = new Target();

        $error = '';

        try {
            $target->value = $this->timeRangeParser->parse($string);
            $target->type = Target::TYPE_TIME_RANGE;
            return $target;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        try {
            $target->value = $this->moneyRangeParser->parse($string);
            $target->type = Target::TYPE_MONEY_RANGE;
            return $target;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        throw new Exception("Cant parse target range. $error");
    }

    private function isRange(string $string): bool
    {
        if (strpos($string, '-') !== false) {
            return true;
        }
        return false;
    }
}

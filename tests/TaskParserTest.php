<?php

declare(strict_types=1);

namespace Test;

use App\Project\Parsers\MoneyParser;
use App\Project\Parsers\MoneyRangeParser;
use App\Project\Parsers\TargetParser;
use App\Project\Parsers\TaskParser;
use App\Project\Parsers\TimeParser;
use App\Project\Parsers\TimeRangeParser;
use PHPUnit\Framework\TestCase;

final class TaskParserTest extends TestCase
{
    private $taskParser;

    public function setUp(): void
    {
        $timeParser = new TimeParser();
        $timeRangeParser = new TimeRangeParser($timeParser);
        $moneyParser = new MoneyParser();
        $moneyRangeParser = new MoneyRangeParser($moneyParser);

        $targetParser = new TargetParser($timeParser, $timeRangeParser, $moneyParser, $moneyRangeParser);
        $this->taskParser = new TaskParser($targetParser);
    }

    public function testCanParseTask1(): void
    {
        $report = $this->taskParser->parse('- Das ist eine Aufgabe; 2,5h');
        var_dump($report);
    }

    public function testCanParseTask2(): void
    {
        $report = $this->taskParser->parse('- Das ist eine Aufgabe; 2h - 4h');
        var_dump($report);
    }

    public function testCanParseTask3(): void
    {
        $report = $this->taskParser->parse('- Das ist eine Aufgabe; 80,00€ - 90,00€');
        var_dump($report);
    }
}

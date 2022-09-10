<?php

declare(strict_types=1);

namespace Test;

use RobinTheHood\TextProjectManager\Project\Parsers\AmountParser;
use RobinTheHood\TextProjectManager\Project\Parsers\MoneyParser;
use RobinTheHood\TextProjectManager\Project\Parsers\ReportParser;
use RobinTheHood\TextProjectManager\Project\Parsers\TimeParser;
use PHPUnit\Framework\TestCase;

final class ReportParserTest extends TestCase
{
    private $reportParser;

    public function setUp(): void
    {
        $timeParser = new TimeParser();
        $moneyParser = new MoneyParser();
        $amountParser = new AmountParser($timeParser);
        $this->reportParser = new ReportParser($amountParser, $moneyParser);
    }

    public function testCanParseReport1(): void
    {
        $report = $this->reportParser->parse('++ 17.07.2022; 2h; Das ist ein Report; 80,00€; 60,00€');
        var_dump($report);
    }

    public function testCanParseReport2(): void
    {
        $report = $this->reportParser->parse('++ 17.07.2022; 37min; Das ist ein Report');
        var_dump($report);
    }

    public function testCanParseReport3(): void
    {
        $report = $this->reportParser->parse('++ 17.07.2022; 2x; Das ist ein Report; 80,00€');
        var_dump($report);
    }
}

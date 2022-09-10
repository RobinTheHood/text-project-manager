<?php

declare(strict_types=1);

namespace Test;

use RobinTheHood\TextProjectManager\Project\Entities\Money;
use RobinTheHood\TextProjectManager\Project\Entities\MoneyRange;
use RobinTheHood\TextProjectManager\Project\Parsers\MoneyParser;
use RobinTheHood\TextProjectManager\Project\Parsers\MoneyRangeParser;
use Exception;
use PHPUnit\Framework\TestCase;

final class MoneyRangeParserTest extends TestCase
{
    private $moneyRangeParser;

    public function setUp(): void
    {
        $timeParser = new MoneyParser();
        $this->moneyRangeParser = new MoneyRangeParser($timeParser);
    }

    public function testCanParseMoneyRange(): void
    {
        $moneyRange = $this->moneyRangeParser->parse('80,00€ - 90,00€');

        $expectedMoneyRange = $this->createMoneyRange('80,00', '90,00');
        $this->assertEquals($expectedMoneyRange, $moneyRange);
    }

    public function testCanParseUnformatedMoneyRange(): void
    {
        $moneyRange = $this->moneyRangeParser->parse(' 80,00 € - 90,00 €');

        $expectedMoneyRange = $this->createMoneyRange('80,00', '90,00');
        $this->assertEquals($expectedMoneyRange, $moneyRange);
    }

    public function testThatMoneyRangeParserThorwsException(): void
    {
        $this->expectException(Exception::class);
        $moneyRange = $this->moneyRangeParser->parse('80,00€ - 90,00€ - 100,00€');
    }

    private function createMoneyRange(string $start, string $end): MoneyRange
    {
        $startMoney = new Money();
        $startMoney->value = $start;

        $endMoney = new Money();
        $endMoney->value = $end;

        $moneyRange = new MoneyRange();
        $moneyRange->startMoney =  $startMoney;
        $moneyRange->endMoney = $endMoney;

        return $moneyRange;
    }
}

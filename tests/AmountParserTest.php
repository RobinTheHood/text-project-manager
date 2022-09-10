<?php

declare(strict_types=1);

namespace Test;

use RobinTheHood\TextProjectManager\Project\Entities\Time;
use RobinTheHood\TextProjectManager\Project\Entities\Amount;
use RobinTheHood\TextProjectManager\Project\Parsers\AmountParser;
use RobinTheHood\TextProjectManager\Project\Parsers\TimeParser;
use PHPUnit\Framework\TestCase;

final class AmountParserTest extends TestCase
{
    private $amountParser;

    public function setUp(): void
    {
        $timeParser = new TimeParser();
        $this->amountParser = new AmountParser($timeParser);
    }

    public function testCanParseAmountTimeHour(): void
    {
        $amount = $this->amountParser->parse('2,4h');

        $expectedTime = new Time();
        $expectedTime->value = '2,4';
        $expectedTime->type = Time::TYPE_HOUR;

        $expectedAmount = new Amount();
        $expectedAmount->value = $expectedTime;
        $expectedAmount->type = Amount::TYPE_TIME;

        $this->assertEquals($expectedAmount, $amount);
    }

    public function testCanParseAmountTimeMinute(): void
    {
        $amount = $this->amountParser->parse('36min');

        $expectedTime = new Time();
        $expectedTime->value = '36';
        $expectedTime->type = Time::TYPE_MINUTE;

        $expectedAmount = new Amount();
        $expectedAmount->value = $expectedTime;
        $expectedAmount->type = Amount::TYPE_TIME;

        $this->assertEquals($expectedAmount, $amount);
    }

    public function testCanParseAmountTimeMixed(): void
    {
        $amount = $this->amountParser->parse('2:30');

        $expectedTime = new Time();
        $expectedTime->value = '2:30';
        $expectedTime->type = Time::TYPE_MIXED;

        $expectedAmount = new Amount();
        $expectedAmount->value = $expectedTime;
        $expectedAmount->type = Amount::TYPE_TIME;

        $this->assertEquals($expectedAmount, $amount);
    }

    public function testCanParseAmountQuantity(): void
    {
        $amount = $this->amountParser->parse('10x');

        $expectedAmount = new Amount();
        $expectedAmount->value = '10';
        $expectedAmount->type = Amount::TYPE_QUANTITY;

        $this->assertEquals($expectedAmount, $amount);
    }
}

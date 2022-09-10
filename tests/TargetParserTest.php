<?php

declare(strict_types=1);

namespace Test;

use App\Project\Entities\Money;
use App\Project\Entities\MoneyRange;
use App\Project\Entities\Target;
use App\Project\Entities\Time;
use App\Project\Entities\TimeRange;
use App\Project\Parsers\MoneyParser;
use App\Project\Parsers\MoneyRangeParser;
use App\Project\Parsers\TargetParser;
use App\Project\Parsers\TimeParser;
use App\Project\Parsers\TimeRangeParser;
use PHPUnit\Framework\TestCase;

final class TargetParserTest extends TestCase
{
    private $targetParser;

    public function setUp(): void
    {
        $timeParser = new TimeParser();
        $timeRangeParser = new TimeRangeParser($timeParser);
        $moneyParser = new MoneyParser();
        $moneyRangeParser = new MoneyRangeParser($moneyParser);
        $this->targetParser = new TargetParser($timeParser, $timeRangeParser, $moneyParser, $moneyRangeParser);
    }

    public function testCanParseTargetTime(): void
    {
        $target = $this->targetParser->parse('2h');

        $expectedTime = new Time();
        $expectedTime->value = '2';
        $expectedTime->type = Time::TYPE_HOUR;

        $expectedTarget = new Target();
        $expectedTarget->value = $expectedTime;
        $expectedTarget->type = Target::TYPE_TIME;

        $this->assertEquals($expectedTarget, $target);
    }

    public function testCanParseTargetTimeRange(): void
    {
        $target = $this->targetParser->parse('2h - 4h');

        $expectedStartTime = new Time();
        $expectedStartTime->value = '2';
        $expectedStartTime->type = Time::TYPE_HOUR;

        $expectedEndTime = new Time();
        $expectedEndTime->value = '4';
        $expectedEndTime->type = Time::TYPE_HOUR;

        $expectedTimeRange = new TimeRange();
        $expectedTimeRange->startTime = $expectedStartTime;
        $expectedTimeRange->endTime = $expectedEndTime;

        $expectedTarget = new Target();
        $expectedTarget->value = $expectedTimeRange;
        $expectedTarget->type = Target::TYPE_TIME_RANGE;

        $this->assertEquals($expectedTarget, $target);
    }

    public function testCanParseTargetMoney(): void
    {
        $target = $this->targetParser->parse('80,99€');

        $expectedMoney = new Money();
        $expectedMoney->value = '80,99';

        $expectedTarget = new Target();
        $expectedTarget->value = $expectedMoney;
        $expectedTarget->type = Target::TYPE_MONEY;

        $this->assertEquals($expectedTarget, $target);
    }

    public function testCanParseTargetMoneyRange(): void
    {
        $target = $this->targetParser->parse('80,99€ - 120,44€');

        $expectedStartMoney = new Money();
        $expectedStartMoney->value = '80,99';

        $expectedEndMoney = new Money();
        $expectedEndMoney->value = '120,44';

        $expectedMoneyRange = new MoneyRange();
        $expectedMoneyRange->startMoney = $expectedStartMoney;
        $expectedMoneyRange->endMoney = $expectedEndMoney;

        $expectedTarget = new Target();
        $expectedTarget->value = $expectedMoneyRange;
        $expectedTarget->type = Target::TYPE_MONEY_RANGE;

        $this->assertEquals($expectedTarget, $target);
    }
}

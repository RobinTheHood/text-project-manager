<?php

declare(strict_types=1);

namespace Test;

use RobinTheHood\TextProjectManager\Project\Entities\Time;
use RobinTheHood\TextProjectManager\Project\Parsers\TimeParser;
use Exception;
use PHPUnit\Framework\TestCase;

final class TimeParserTest extends TestCase
{
    public function testCanParseTimeMinutes(): void
    {
        $timeParser = new TimeParser();
        $time = $timeParser->parse('13min');

        $expectedTime = new Time();
        $expectedTime->value = '13';
        $expectedTime->type = Time::TYPE_MINUTE;

        $this->assertEquals($expectedTime, $time);
    }

    public function testCanParseUnformatedTimeMinutes(): void
    {
        $timeParser = new TimeParser();
        $time = $timeParser->parse(' 13 min');

        $expectedTime = new Time();
        $expectedTime->value = '13';
        $expectedTime->type = Time::TYPE_MINUTE;

        $this->assertEquals($expectedTime, $time);
    }

    public function testCanParseTimeHours(): void
    {
        $timeParser = new TimeParser();

        $time = $timeParser->parse('13,7h');
        $expectedTime = new Time();
        $expectedTime->value = '13,7';
        $expectedTime->type = Time::TYPE_HOUR;
        $this->assertEquals($expectedTime, $time);
    }

    public function testCanParseUnformatedTimeHours(): void
    {
        $timeParser = new TimeParser();
        $time = $timeParser->parse(' 13,7 h');

        $expectedTime = new Time();
        $expectedTime->value = '13,7';
        $expectedTime->type = Time::TYPE_HOUR;

        $this->assertEquals($expectedTime, $time);
    }

    public function testCanParseTimeMixed(): void
    {
        $timeParser = new TimeParser();

        $time = $timeParser->parse('13:60');
        $expectedTime = new Time();
        $expectedTime->value = '13:60';
        $expectedTime->type = Time::TYPE_MIXED;
        $this->assertEquals($expectedTime, $time);
    }

    public function testTimeParserThrowsException(): void
    {
        $this->expectException(Exception::class);

        $timeParser = new TimeParser();
        $time = $timeParser->parse(' 13 min.');
    }
}

<?php

declare(strict_types=1);

namespace Test;

use App\Project\Entities\Time;
use App\Project\Entities\TimeRange;
use App\Project\Parsers\TimeParser;
use App\Project\Parsers\TimeRangeParser;
use Exception;
use PHPUnit\Framework\TestCase;

final class TimeRangeParserTest extends TestCase
{
    private $timeRangeParser;

    public function setUp(): void
    {
        $timeParser = new TimeParser();
        $this->timeRangeParser = new TimeRangeParser($timeParser);
    }

    public function testCanParseTimeRange(): void
    {
        $timeRange = $this->timeRangeParser->parse('2h - 4h');

        $expectedTimeRange = $this->createTimeRange('2', '4');
        $this->assertEquals($expectedTimeRange, $timeRange);
    }

    public function testCanParseUnformatedTimeRange(): void
    {
        $timeRange = $this->timeRangeParser->parse('2 h - 4 h');

        $expectedTimeRange = $this->createTimeRange('2', '4');
        $this->assertEquals($expectedTimeRange, $timeRange);
    }

    public function testThatTimeRangeParserThorwsException(): void
    {
        $this->expectException(Exception::class);
        $timeRange = $this->timeRangeParser->parse('2h - 4h - 5h');
    }

    private function createTimeRange(string $start, string $end): TimeRange
    {
        $startTime = new Time();
        $startTime->value = $start;
        $startTime->type = Time::TYPE_HOUR;

        $endTime = new Time();
        $endTime->value = $end;
        $endTime->type = Time::TYPE_HOUR;

        $timeRange = new TimeRange();
        $timeRange->startTime =  $startTime;
        $timeRange->endTime = $endTime;

        return $timeRange;
    }
}

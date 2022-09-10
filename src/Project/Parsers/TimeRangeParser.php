<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use RobinTheHood\TextProjectManager\Helpers\StringHelper;
use RobinTheHood\TextProjectManager\Project\Entities\TimeRange;
use RobinTheHood\TextProjectManager\Project\Interfaces\TimeParserInterface;
use RobinTheHood\TextProjectManager\Project\Interfaces\TimeRangeParserInterface;
use Exception;

class TimeRangeParser implements TimeRangeParserInterface
{
    /**
     * @var TimeParserInterface
     */
    private $timeParser;

    public function __construct(TimeParserInterface $timeParser)
    {
        $this->timeParser = $timeParser;
    }

    public function parse(string $string): TimeRange
    {
        $parts = StringHelper::getTrimmedLineParts($string, '-');

        if (count($parts) !== 2) {
            throw new Exception("This is not a valid TimeRange");
        }

        $startTime = $this->timeParser->parse($parts[0]);
        $endTime = $this->timeParser->parse($parts[1]);

        $timeRange = new TimeRange();
        $timeRange->startTime = $startTime;
        $timeRange->endTime = $endTime;

        return $timeRange;
    }
}

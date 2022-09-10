<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Interfaces;

use RobinTheHood\TextProjectManager\Project\Entities\TimeRange;

interface TimeRangeParserInterface
{
    public function parse(string $string): TimeRange;
}

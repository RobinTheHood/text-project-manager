<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

use App\Project\Entities\TimeRange;

interface TimeRangeParserInterface
{
    public function parse(string $string): TimeRange;
}

<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

use App\Project\Entities\Time;

interface TimeParserInterface
{
    public function parse(string $string): Time;
}

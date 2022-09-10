<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Interfaces;

use RobinTheHood\TextProjectManager\Project\Entities\Time;

interface TimeParserInterface
{
    public function parse(string $string): Time;
}

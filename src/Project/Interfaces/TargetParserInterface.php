<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Interfaces;

use RobinTheHood\TextProjectManager\Project\Entities\Target;

interface TargetParserInterface
{
    public function parse(string $string): Target;
}

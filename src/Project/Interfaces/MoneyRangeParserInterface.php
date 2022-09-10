<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Interfaces;

use RobinTheHood\TextProjectManager\Project\Entities\MoneyRange;

interface MoneyRangeParserInterface
{
    public function parse(string $string): MoneyRange;
}

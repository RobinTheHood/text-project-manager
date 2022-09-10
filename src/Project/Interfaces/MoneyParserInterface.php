<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Interfaces;

use RobinTheHood\TextProjectManager\Project\Entities\Money;

interface MoneyParserInterface
{
    public function parse(string $string): Money;
}

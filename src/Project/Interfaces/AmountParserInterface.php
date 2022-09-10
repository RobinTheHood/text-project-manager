<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Interfaces;

use RobinTheHood\TextProjectManager\Project\Entities\Amount;

interface AmountParserInterface
{
    public function parse(string $string): Amount;
}

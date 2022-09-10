<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Interfaces;

interface PriceParserInterface
{
    public function parse(string $string): float;
}

<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

interface TimeParserInterface
{
    public function parse(string $string): array;
}

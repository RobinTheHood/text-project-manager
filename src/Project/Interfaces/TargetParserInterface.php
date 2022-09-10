<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

use App\Project\Entities\Target;

interface TargetParserInterface
{
    public function parse(string $string): Target;
}

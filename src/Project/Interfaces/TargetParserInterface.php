<?php

declare(strict_types=1);

namespace App\Project\Interfaces;

use App\ProjectParser\Parser\Entities\Target;

interface TargetParserInterface
{
    public function parse(string $string): Target;
}

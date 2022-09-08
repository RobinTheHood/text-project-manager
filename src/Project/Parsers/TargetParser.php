<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Interfaces\TargetParserInterface;
use App\Project\Interfaces\TimeParserInterface;
use App\ProjectParser\Parser\Entities\Target;

class TargetParser implements TargetParserInterface
{
    private $timeParser;

    public function __construct(TimeParserInterface $timeParser)
    {
        $this->timeParser = $timeParser;
    }

    public function parse(string $string): Target
    {
        $target = new Target();
        $target->type == Target::TYPE_NONE;

        if (!$string) {
            return $target;
        }

        $value = $this->timeParser->parse($string);
        $target->value = $value;
        $target->type = Target::TYPE_TIME;

        return $target;
    }
}

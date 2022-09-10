<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use RobinTheHood\TextProjectManager\Helpers\StringHelper;
use RobinTheHood\TextProjectManager\Project\Entities\Task;
use RobinTheHood\TextProjectManager\Project\Interfaces\TargetParserInterface;
use Exception;

class TaskParser
{
    /**
     * @var TargetParserInterface
     */
    private $targetParser;

    public function __construct(TargetParserInterface $targetParser)
    {
        $this->targetParser = $targetParser;
    }

    public function parse(string $string): ?Task
    {
        $string = trim($string);

        if (!$this->isValidTaskLineStart($string)) {
            return null;
        }

        $string = StringHelper::skipLetters($string, 2);
        $stringParts = StringHelper::getTrimmedLineParts($string, ';');

        $name = $stringParts[0] ?? '';
        if (!$name) {
            throw new Exception('Missing task name');
        }

        $target = $this->targetParser->parse($stringParts[1] ?? '');

        $task = new Task();
        $task->name = $name;
        $task->target = $target;

        return $task;
    }

    /**
     * Kontrolliert ob es sich um eine um eine Zeile mit einem validen Zeichen
     * Anfang für einen Task handelt. Ein Task fängt mit einem - an und darf danach
     * nicht direkt einen weiteren - haben.
     */
    private function isValidTaskLineStart(string $string): bool
    {
        $char0 = mb_substr($string, 0, 1, 'utf-8');
        $char1 = mb_substr($string, 1, 1, 'utf-8');

        if ($char0 === '-' && $char1 !== '-') {
            return true;
        }

        return false;
    }
}

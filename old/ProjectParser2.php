<?php

namespace RobinTheHood\TextProjectManager\Project;

use Exception;

class ProjectParser2
{
    public const SUBTASK_STATUS_BILLABLE = 1;
    public const SUBTASK_STATUS_UNBILLABLE = 2;

    private $lineNumber = 0;

    /**
     * @return Task[]
     */
    public function parseTasks(): array
    {
        $fileContent = file_get_contents(__DIR__ . '/data/ProjectPlan01.txt');
        $lines = explode("\n", $fileContent);

        $tasks = [];

        /**
         * @var Task
         */
        $currentTask = null;

        foreach ($lines as $line) {
            $this->lineNumber++;

            $task = $this->tryParseTask($line);
            if ($task) {
                if ($currentTask) {
                    $tasks[] = $currentTask;
                }

                $currentTask = $task;
                continue;
            }

            $report = $this->tryParseReport($line);
            if ($report && $currentTask) {
                $currentTask->reports[] = $report;
            }
        }

        if ($currentTask) {
            $tasks[] = $currentTask;
        }

        return $tasks;
    }

    private function tryParseTask(string $string): ?Task
    {
        $string = trim($string);

        if (!$this->isValidTaskLineStart($string)) {
            return null;
        }

        $string = $this->skipLetters($string, 2);
        $stringParts = $this->getTrimmedLineParts($string);

        $name = $stringParts[0] ?? '';
        if (!$name) {
            throw new Exception('Missing task name in line:' . $this->lineNumber);
        }

        $target = $this->parseTarget($lineParts[1] ?? '', $this->lineNumber);

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

    private function tryParseReport(string $string): ?Report
    {
        $string = trim($string);

        if (!$this->isValidReportLineStart($string)) {
            return null;
        }

        $typeString = mb_substr($string, 0, 2, 'utf-8');

        $type = Report::TYPE_BILLABLE;
        if ($typeString == '++') {
            $type = Report::TYPE_BILLABLE;
        } elseif ($typeString == '--') {
            $type = Report::TYPE_UNBILLABLE;
        }

        $string = $this->skipLetters($string, 2);
        $stringParts = $this->getTrimmedLineParts($string);

        $description = $stringParts[2] ?? '';
        $date = $stringParts[0] ?? '';
        $amount = $this->parseAmount($stringParts[1] ?? '');

        $externalPrice = $this->parsePrice($stringParts[3] ?? '');
        $internalPrice = $this->parsePrice($stringParts[4] ?? '');

        $report = new Report();
        $report->description = $description;
        $report->type = $type;
        $report->date = $date;
        $report->amount = $amount;
        $report->externalPrice = $externalPrice;
        $report->internalPrice = $internalPrice;

        return $report;
    }

    /**
     * Kontrolliert ob es sich um eine um eine Zeile mit einem validen Zeichen
     * Anfang für einen Task handelt. Ein Task fängt mit einem - an und darf danach
     * nicht direkt einen weiteren - haben.
     */
    private function isValidReportLineStart(string $string): bool
    {
        $char0 = mb_substr($string, 0, 1, 'utf-8');
        $char1 = mb_substr($string, 1, 1, 'utf-8');
        $char2 = mb_substr($string, 2, 1, 'utf-8');

        if ($char0 === '-' && $char1 === '-' && $char2 !== '-') {
            return true;
        }

        if ($char0 === '+' && $char1 === '+' && $char2 !== '+') {
            return true;
        }

        if ($char0 === '?' && $char1 === '?' && $char2 !== '?') {
            return true;
        }

        return false;
    }

    /**
     * Trennt einen String anhand von ; auf und trimmt jeden Teil.
     */
    private function getTrimmedLineParts(string $string): array
    {
        $stringParts = explode(';', $string);
        $trimmedStringParts = array_map('trim', $stringParts);
        return $trimmedStringParts;
    }

    /**
     * Gibt den Rest eines Strings zurück und überspringt eine
     * beliebige Anzahl am Anfang des Strings.
     */
    private function skipLetters(string $string, int $count): string
    {
        return substr($string, $count, strlen($string) - $count);
    }

    private function parsePrice(string $string): float
    {
        $valueString = str_replace(',', '.', $string);
        return floatval($valueString);
    }

    private function parseTarget(string $string): Target
    {
        $target = new Target();
        $target->type == Target::TYPE_NONE;

        if (!$string) {
            return $target;
        }

        $value = $this->parseTime($string);
        $target->value = $value;
        $target->type = Target::TYPE_TIME;

        return $target;
    }

    private function parseTime(string $string): int
    {
        if (strpos($string, 'min') !== false) {
            $unit = 'min';
            $valueStr = str_replace('min', '', $string);
        } elseif (strpos($string, 'h') !== false) {
            $unit = 'h';
            $valueStr = str_replace('h', '', $string);
        } else {
            throw new Exception("Unkown unit in TargetTime field on line $this->lineNumber");
        }

        $value = floatval($valueStr);

        if ($unit == 'h') {
            $value *= 60;
        }

        return $value;
    }

    private function parseAmount(string $string): Amount
    {
        $amount = new Amount();

        $valueStr = '';
        $value = 0.0;
        $type = Amount::TYPE_TIME;

        if (strpos($string, 'min') !== false) {
            $valueStr = str_replace('min', '', $string);
            $value = floatval($valueStr);
            $type = Amount::TYPE_TIME;
        } elseif (strpos($string, 'h') !== false) {
            $valueStr = str_replace('h', '', $string);
            $value = floatval($valueStr) * 60;
            $type = Amount::TYPE_TIME;
        } elseif (strpos($string, 'x') !== false) {
            $valueStr = str_replace('x', '', $string);
            $value = floatval($valueStr);
            $type = Amount::TYPE_FIX;
        } else {
            throw new Exception("Unkown unit in Qunatity field on line $this->lineNumber");
        }

        $amount->type = $type;
        $amount->value = $value;

        return $amount;
    }
}

// Target time, actual time



if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

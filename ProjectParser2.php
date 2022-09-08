<?php

namespace App\Project;

use Exception;

class ProjectParser2
{
    public const SUBTASK_STATUS_BILLABLE = 1;
    public const SUBTASK_STATUS_UNBILLABLE = 2;

    /**
     * @return Tasks
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
        $lineNumber = 0;
        foreach ($lines as $line) {
            $lineNumber++;

            $task = $this->tryParseTask($line, $lineNumber);
            if ($task) {
                if ($currentTask) {
                    $tasks[] = $currentTask;
                }

                $currentTask = $task;
                continue;
            }

            $report = $this->tryParseReport($line, $lineNumber);
            if ($report && $currentTask) {
                $currentTask->reports[] = $report;
            }
        }

        if ($currentTask) {
            $tasks[] = $currentTask;
        }

        return $tasks;
    }

    private function tryParseTask(string $line, int $linenumber): ?Task
    {
        $line = trim($line);

        if (!$this->isValidTaskLineStart($line, $linenumber)) {
            return null;
        }

        $line = $this->skipLetters($line, 2);
        $lineParts = $this->getTrimmedLineParts($line);

        $name = $lineParts[0] ?? '';
        if (!$name) {
            throw new Exception('Missing task name in line:' . $linenumber);
        }

        $target = $this->parseTarget($lineParts[1] ?? '', $linenumber);

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
    private function isValidTaskLineStart(string $line, int $lineNumber): bool
    {
        $char0 = mb_substr($line, 0, 1, 'utf-8');
        $char1 = mb_substr($line, 1, 1, 'utf-8');

        if ($char0 === '-' && $char1 !== '-') {
            return true;
        }

        return false;
    }

    private function tryParseReport(string $line, int $linenumber): ?Report
    {
        $line = trim($line);

        if (!$this->isValidReportLineStart($line, $linenumber)) {
            return null;
        }

        $typeString = mb_substr($line, 0, 2, 'utf-8');

        $type = Report::TYPE_BILLABLE;
        if ($typeString == '++') {
            $type = Report::TYPE_BILLABLE;
        } elseif ($typeString == '--') {
            $type = Report::TYPE_UNBILLABLE;
        }

        $line = $this->skipLetters($line, 2);
        $lineParts = $this->getTrimmedLineParts($line);

        $description = $lineParts[2] ?? '';
        $date = $lineParts[0] ?? '';
        $amount = $this->parseAmount($lineParts[1] ?? '', $linenumber);

        $externalPrice = $this->parsePrice($lineParts[3] ?? '', $linenumber);
        $internalPrice = $this->parsePrice($lineParts[4] ?? '', $linenumber);

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
    private function isValidReportLineStart(string $line, int $lineNumber): bool
    {
        $char0 = mb_substr($line, 0, 1, 'utf-8');
        $char1 = mb_substr($line, 1, 1, 'utf-8');
        $char2 = mb_substr($line, 2, 1, 'utf-8');

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
    private function getTrimmedLineParts(string $line): array
    {
        $lineParts = explode(';', $line);
        $trimmedLineParts = array_map('trim', $lineParts);
        return $trimmedLineParts;
    }

    /**
     * Gibt den Rest eines Strings zurück und überspringt eine
     * beliebige Anzahl am Anfang des Strings.
     */
    private function skipLetters(string $string, int $count): string
    {
        return substr($string, $count, strlen($string) - $count);
    }

    private function parsePrice(string $string, int $linenumber): float
    {
        $valueString = str_replace(',', '.', $string);
        return floatval($valueString);
    }

    private function parseTarget(string $string, int $lineNumber): Target
    {
        $target = new Target();
        $target->type == Target::TYPE_NONE;

        if (!$string) {
            return $target;
        }

        $value = $this->parseTime($string, $lineNumber);
        $target->value = $value;
        $target->type = Target::TYPE_TIME;

        return $target;
    }

    private function parseTime(string $string, int $lineNumber): int
    {
        if (strpos($string, 'min') !== false) {
            $unit = 'min';
            $valueStr = str_replace('min', '', $string);
        } elseif (strpos($string, 'h') !== false) {
            $unit = 'h';
            $valueStr = str_replace('h', '', $string);
        } else {
            throw new Exception("Unkown unit in TargetTime field on line $lineNumber");
        }

        $value = floatval($valueStr);

        if ($unit == 'h') {
            $value *= 60;
        }

        return $value;
    }

    private function parseAmount(string $string, int $lineNumber): Amount
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
            throw new Exception("Unkown unit in Qunatity field on line $lineNumber");
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

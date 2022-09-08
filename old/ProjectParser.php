<?php

namespace App\Project;

use Exception;

class ProjectParser
{
    public const SUBTASK_STATUS_BILLABLE = 1;
    public const SUBTASK_STATUS_UNBILLABLE = 2;

    public function parseTasks(): array
    {
        $fileContent = file_get_contents(__DIR__ . '/data/ProjectPlan01.txt');
        $lines = explode("\n", $fileContent);

        $tasks = [];
        $currentTask = [];
        $lineNumber = 0;
        foreach ($lines as $line) {
            $lineNumber++;

            $task = $this->parseTask($line, $lineNumber);
            if ($task) {
                if ($currentTask) {
                    $tasks[] = $currentTask;
                }

                $currentTask = $task;
                continue;
            }

            $subTask = $this->parseSubTask($line, $lineNumber);
            if ($subTask && $currentTask) {
                $currentTask['subTasks'][] = $this->parseSubTask($line, $lineNumber);
            }
        }

        if ($currentTask) {
            $tasks[] = $currentTask;
        }

        return $tasks;
    }

    private function parseTask(string $line, int $linenumber): array
    {
        $line = trim($line);

        if (!$this->isValidTaskLineStart($line, $linenumber)) {
            return [];
        }

        $line = $this->skipLetters($line, 2);
        $lineParts = $this->getTrimmedLineParts($line);

        $name = $lineParts[0] ?? '';
        if (!$name) {
            throw new Exception('Missing task name in line:' . $linenumber);
        }

        $targetTime = $this->parseTime($lineParts[1] ?? '', $linenumber);

        return [
            'name' => $name,
            'targetTime' => $targetTime,
            'subTasks' => []
        ];
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

    private function parseSubTask(string $line, int $linenumber): array
    {
        $line = trim($line);

        if (!$this->isValidSubTaskLineStart($line, $linenumber)) {
            return [];
        }

        $statusString = mb_substr($line, 0, 2, 'utf-8');

        $status = self::SUBTASK_STATUS_BILLABLE;
        if ($statusString == '++') {
            $status = self::SUBTASK_STATUS_BILLABLE;
        } elseif ($statusString == '--') {
            $status = self::SUBTASK_STATUS_UNBILLABLE;
        }

        $line = $this->skipLetters($line, 2);
        $lineParts = $this->getTrimmedLineParts($line);

        $name = $lineParts[2] ?? '';
        $date = $lineParts[0] ?? '';
        $quantity = $this->parseQuantity($lineParts[1] ?? '', $linenumber);

        $externalPrice = $this->parsePrice($lineParts[3] ?? '', $linenumber);
        $internalPrice = $this->parsePrice($lineParts[4] ?? '', $linenumber);

        return [
            'name' => $name ?? 'Unteraufgabe ohne Name',
            'status' => $status,
            'date' => $date,
            'quantity' => $quantity,
            'externalPrcie' => $externalPrice,
            'internalPrice' => $internalPrice
        ];
    }

    /**
     * Kontrolliert ob es sich um eine um eine Zeile mit einem validen Zeichen
     * Anfang für einen Task handelt. Ein Task fängt mit einem - an und darf danach
     * nicht direkt einen weiteren - haben.
     */
    private function isValidSubTaskLineStart(string $line, int $lineNumber): bool
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

    private function parseTime(string $string, int $lineNumber): array
    {
        if (!$string) {
            return [];
        }

        $unit = '';
        $valueStr = '';

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

        return [
            'value' => $value,
            'unit' => $unit
        ];
    }

    private function parseQuantity(string $string, int $lineNumber): array
    {
        $unit = '';
        $valueStr = '';
        if (strpos($string, 'min') !== false) {
            $unit = 'min';
            $valueStr = str_replace('min', '', $string);
        } elseif (strpos($string, 'h') !== false) {
            $unit = 'h';
            $valueStr = str_replace('h', '', $string);
        } elseif (strpos($string, 'x') !== false) {
            $unit = 'x';
            $valueStr = str_replace('x', '', $string);
        } else {
            throw new Exception("Unkown unit in Qunatity field on line $lineNumber");
        }

        $value = floatval($valueStr);

        return [
            'value' => $value,
            'unit' => $unit
        ];
    }
}


if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

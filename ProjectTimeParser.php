<?php

namespace App;

use DateTime;
use Exception;

class ProjectTimeParser
{
    public const SUBTASK_STATUS_BILLABLE = 1;
    public const SUBTASK_STATUS_UNBILLABLE = 2;

    public function parseTasks(): array
    {
        $fileContent = file_get_contents(__DIR__ . '/abrechnung.txt');
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

// Target time, actual time


class ProjectEvaluation
{

    public function createBillItems(array $tasks): string
    {
        $roundedTotalPrice = 0;

        $string = "####### RECHNUNGSPOSITIONEN #######\n";
        foreach ($tasks as $task) {
            $billableSubTasks = $this->getBillableSubTasksFromTask($task);
            if (!$billableSubTasks) {
                continue;
            }

            $firstSubTask = $this->getFirstSubTaskFromSubTasksByDate($billableSubTasks);
            $lastSubTask = $this->getLastSubTaskFromSubTasksByDate($billableSubTasks);

            $string .= "{$task['name']} ({$firstSubTask['date']} - {$lastSubTask['date']})\n";

            $string .= $this->getFormatedSubTaskNamesFromSubTask($billableSubTasks);
            $billabledHours = $this->getHoursFromSubTasks($billableSubTasks);
            $roundedBillableHours = $this->stepRoundHours($billabledHours, 0.25);
            $roundedBillablePrice = $roundedBillableHours * 80.0;
            $formatedRoundedBillablePrice = number_format($roundedBillablePrice, 2, ',', '.');

            if ($task['targetTime']['value'] ?? 0) {
                $roundedTargetBillableHours = $this->stepRoundHours($task['targetTime']['value'], 0.25);
                $roundedTargetBillablePrice = $roundedTargetBillableHours * 80.0;
                $formatedRoundedTargetBillablePrice = number_format($roundedTargetBillablePrice, 2, ',', '.');
                $string .= "$roundedTargetBillableHours Std. à 80,00€ = {$formatedRoundedTargetBillablePrice}€\n\n";
            } else {
                $string .= "$roundedBillableHours Std. à 80,00€ = {$formatedRoundedBillablePrice}€\n\n";
            }
            $roundedTotalPrice += $roundedBillablePrice;
        }

        //$formatedRoundedTotalPrice = number_format($roundedTotalPrice, 2, ',', '.');
        //$string .= "Gesamt\n{$formatedRoundedTotalPrice}€\n";

        return $string;
    }

    /**
     * Deckungsbeitragsberechnung:
     * https://www.textbest.de/magazin/aus-dem-agenturalltag-diese-controlling-kennzahlen-sind-wichtig/
     * Bezahlte Stunden x Stundensatz im KVA – erbrachte Stunden x Kosten pro Stunde = Deckungsbeitrag
     */
    public function createEvaluation(array $tasks): string
    {
        $internalPrice = 60;
        $externalPrice = 80;

        $roundedTotalHours = 0;
        $totalContributionMargin = 0;

        $string = "####### AUSWERTUNG #######\n";
        $string .= "### Basiswerte ###\n";
        $string .= "60,00 € interner Stundensatz (Agentur-Kosten pro Stunde, Büro, Strom, Personal, ...)\n";
        $string .= "80,00 € externer Stundensatz (Kunden-Kosten pro Stunde)\n";
        $string .= "\n";

        $string .= "### Projekt-Aufgaben ###\n";

        foreach ($tasks as $task) {
            $subTasks = $task['subTasks'];
            $billableSubTasks = $this->getBillableSubTasksFromTask($task);
            $unbillableSubTasks = $this->getUnbillableSubTasksFromTask($task);

            $firstSubTask = $this->getFirstSubTaskFromSubTasksByDate($subTasks);
            $lastSubTask = $this->getLastSubTaskFromSubTasksByDate($subTasks);

            $string .= "Aufgabe: {$task['name']} ({$firstSubTask['date']} - {$lastSubTask['date']})\n";

            // IST - Dauer
            $actualHours = $this->getHoursFromSubTasks($subTasks);
            $roundedActualHours = $this->stepRoundHours($actualHours, 0.25);

            // SOLL - Dauer
            $roundedTargetHours = $this->stepRoundHours($task['targetTime']['value'] ?? 0, 0.25);

            // BERECHNENBARE - Dauer
            $billabledHours = $this->getHoursFromSubTasks($billableSubTasks);
            $roundedBillableHours = $this->stepRoundHours($billabledHours, 0.25);

            $resultHours = 0;
            if ($roundedTargetHours) {
                $resultHours = $roundedTargetHours - $roundedActualHours;
            } else {
                $resultHours = 0 - ($roundedActualHours - $roundedBillableHours);
            }

            $contributionMargin = 0;
            if ($roundedTargetHours) {
                $contributionMargin = $roundedTargetHours * $externalPrice - $roundedActualHours * $internalPrice;
            } else {
                $contributionMargin = $roundedBillableHours * $externalPrice - $roundedActualHours * $internalPrice;
            }

            $roundedTotalHours += $resultHours;
            $totalContributionMargin += $contributionMargin;

            $formatedRoundedBillableHours = number_format($roundedBillableHours, 2, ',', '.');
            $formatedRoundedActualHours = number_format($roundedActualHours, 2, ',', '.');
            $formatedRoundedTargetHours = number_format($roundedTargetHours, 2, ',', '.');
            $formatedResultHours = number_format($resultHours, 2, ',', '.');
            $formatedContributionMargin = number_format($contributionMargin, 2, ',', '.');

            if ($roundedTargetHours) {
                $string .= "$formatedRoundedActualHours Std. IST-Zeit\n";
                $string .= "$formatedRoundedTargetHours Std. SOLL-Zeit\n";
            } else {
                $string .= "$formatedRoundedActualHours Std. IST-Zeit\n";
                $string .= "$formatedRoundedBillableHours Std. davon Abrechnungsfähig (fiktive SOLL-Zeit)\n";
            }
            $string .= "$formatedResultHours Std. gewonnene/verlorene Zeit\n";
            $string .= "$formatedContributionMargin € Deckungsbeitrag\n";
            $string .= "\n";
        }

        $formatedTotalContributionMargin = number_format($totalContributionMargin, 2, ',', '.');

        $string .= "### Gesamter Auftrag ### \n";
        $string .= "{$roundedTotalHours} Std. gewonnene/verlorene Zeit\n";
        $string .= "$formatedTotalContributionMargin € Deckungsbeitrag\n";

        return $string;
    }

    private function getHoursFromSubTasks(array $subTasks): float
    {
        $totalHours = 0;
        foreach ($subTasks as $subTask) {
            $hours = 0;
            if ($subTask['quantity']['unit'] == 'min') {
                $hours = 1 / 60 * $subTask['quantity']['value'];
            } elseif ($subTask['quantity']['unit'] == 'h') {
                $hours = $subTask['quantity']['value'];
            }
            $totalHours += $hours;
        }
        return $totalHours;
    }

    private function getFirstSubTaskFromSubTasksByDate(array $subTasks): array
    {
        $firstSubTask = null;
        foreach ($subTasks as $subTask) {
            if (!$firstSubTask || $this->compareDate($firstSubTask['date'], $subTask['date']) > 0) {
                $firstSubTask = $subTask;
            }
        }
        return $firstSubTask;
    }

    private function getLastSubTaskFromSubTasksByDate(array $subTasks): array
    {
        $lastSubTask = null;
        foreach ($subTasks as $subTask) {
            if (!$lastSubTask || $this->compareDate($lastSubTask['date'], $subTask['date']) < 0) {
                $lastSubTask = $subTask;
            }
        }
        return $lastSubTask;
    }

    private function filterBillableSubTasks(array $subTasks): array
    {
        $billableSubTasks = [];
        foreach ($subTasks as $subTask) {
            if ($subTask['status'] !== ProjectTimeParser::SUBTASK_STATUS_BILLABLE) {
                continue;
            }
            $billableSubTasks[] = $subTask;
        }
        return $billableSubTasks;
    }

    private function filterUnbillableSubTasks(array $subTasks): array
    {
        $unbillableSubTasks = [];
        foreach ($subTasks as $subTask) {
            if ($subTask['status'] !== ProjectTimeParser::SUBTASK_STATUS_UNBILLABLE) {
                continue;
            }
            $unbillableSubTasks[] = $subTask;
        }
        return $unbillableSubTasks;
    }

    private function getFormatedSubTaskNamesFromSubTask(array $subTasks): string
    {
        $string = '';
        foreach ($subTasks as $subTask) {
            $string .= "- {$subTask['name']} ({$subTask['date']})\n";
        }
        return $string;
    }

    private function getBillableSubTasksFromTask(array $task): array
    {
        return $this->filterBillableSubTasks($task['subTasks']);
    }

    private function getUnbillableSubTasksFromTask(array $task): array
    {
        return $this->filterUnbillableSubTasks($task['subTasks']);
    }

    private function compareDate(string $germanDate1, string $germanDate2): int
    {
        $dateTime1 = new DateTime($germanDate1);
        $dateTime2 = new DateTime($germanDate2);

        if ($dateTime1->getTimestamp() < $dateTime2->getTimestamp()) {
            return -1;
        } elseif ($dateTime1->getTimestamp() > $dateTime2->getTimestamp()) {
            return 1;
        }

        return 0;
    }

    private function processTask(array $task): void
    {
        $stdSum = 0;
        $minSum = 0;
        $minSumNeg = 0;
        foreach ($task['subTasks'] as $subTask) {
            $minSum += $subTask['quantity']['value'];
            if ($subTask['status'] == '-') {
                $minSumNeg += $subTask['quantity']['value'];
            }
        }
        $stdSum = 1 / 60 * $this->stepRound($minSum);
        $stdSumNeg = 1 / 60 * $this->stepRound($minSumNeg);

        $istTime = $stdSum;
        $istValue = $stdSum * 80;
        $istTimeNeg = $stdSumNeg;
        $istValueNeg = $stdSumNeg * 80;
        $sollTime = $task['targetTime']['value'];
        $sollValue = $task['targetTime']['value'] * 80;

        $gewinn = $sollValue - $istValue;

        if ($sollValue == 0) {
            $gewinn = - $istValueNeg;
        }


        echo $task['name'] . "\n";
        echo ' IST: ' . $istTime . ' Std. = ' . $istValue . ' Euro' . "\n";
        echo ' SOLL: ' . $sollTime . ' Std. = ' . $sollValue . ' Euro' . "\n";
        //echo ' Deckungsbeitrag: 30%' . "\n";
        echo ' Gewinn: ' . $gewinn . " Euro \n";
        echo "\n";
    }

    private function processTask2(array $task): void
    {
        $stdSum = 0;
        $minSum = 0;
        foreach ($task['subTasks'] as $subTask) {
            if ($subTask['status'] != '+') {
                continue;
            }
            $minSum += $subTask['quantity']['value'];
        }
        $stdSum = 1 / 60 * $this->stepRound($minSum);

        $istTime = $stdSum;
        $istValue = $stdSum * 80;
        $sollTime = $task['targetTime']['value'];
        $sollValue = $task['targetTime']['value'] * 80;

        $time = $sollTime;
        $value = $sollValue;

        if ($sollTime == 0) {
            $time = $istTime;
            $value = $istValue;
        }

        if ($time == 0) {
            return;
        }

        echo $task['name'] . "\n";
        echo '' . $time . ' Std. = ' . $value . ' Euro' . "\n";
        echo "\n";
    }

    private function stepRoundHours(float $hours, float $step): float
    {
        $minutes = round($hours * 60);
        $minutesStep = round($step * 60);
        return $this->stepRoundMinutes($minutes, $minutesStep) / 60;
    }

    private function stepRoundMinutes(int $minutes, int $step): int
    {
        $roundedMinutes = round($minutes / $step) * $step;
        return $roundedMinutes;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

$projectTimeParser = new ProjectTimeParser();
$tasks = $projectTimeParser->parseTasks();
//var_dump($tasks);

$projectEvaluation = new ProjectEvaluation();
$string = $projectEvaluation->createBillItems($tasks);
//$string = $projectEvaluation->createEvaluation($tasks);
echo $string;

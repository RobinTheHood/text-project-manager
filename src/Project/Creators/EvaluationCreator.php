<?php

declare(strict_types=1);

namespace App\Project\Creators;

use DateTime;

class EvaluationCreator
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
            if ($subTask['status'] !== ProjectParser::SUBTASK_STATUS_BILLABLE) {
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
            if ($subTask['status'] !== ProjectParser::SUBTASK_STATUS_UNBILLABLE) {
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

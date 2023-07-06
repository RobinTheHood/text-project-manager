<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Project;
use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

/**
 * Deckungsbeitragsberechnung:
 * https://www.textbest.de/magazin/aus-dem-agenturalltag-diese-controlling-kennzahlen-sind-wichtig/
 * Bezahlte Stunden x Stundensatz im KVA – erbrachte Stunden x Kosten pro Stunde = Deckungsbeitrag
 *
 * SOLL Einnahmen - IST Ausgaben = Deckungsbeitrag
 */
class EvaluationCreator extends AbstractCreator
{
    private const PRICE_BASE_EXTERNAL = 80.00;
    private const PRICE_BASE_INTERNAL = 60.00;

    public function create(Project $project): string
    {
        $string = '';
        $string .= "####### AUSWERTUNG #######\n";
        $string .= "### Basiswerte ###\n";
        $string .= "60,00 € interner Stundensatz (Agentur-Kosten pro Stunde, Büro, Strom, Personal, ...)\n";
        $string .= "80,00 € externer Stundensatz (Kunden-Kosten pro Stunde)\n";
        $string .= "\n";

        $taskEvaluationDTOs = [];
        foreach ($project->tasks as $task) {
            $this->evalTask($taskEvaluationDTOs, $task);
        }

        $totalContributionMargin = 0;

        /** @var TaskEvaluationDTO $taskEvaluationDTO*/
        foreach ($taskEvaluationDTOs as $taskEvaluationDTO) {
            $string .= $this->renderTaskEvaluationDTO($taskEvaluationDTO);
            $totalContributionMargin += $taskEvaluationDTO->getContributionMargin();
        }

        $string .= "### Gesamter Auftrag ### \n";
        $string .= "{$this->formatCurrency($totalContributionMargin)} Deckungsbeitrag\n";

        return $string;
    }

    /**
     * @param TaskEvaluationDTO[] $taskEvaluationDTOs
     * @param Task $task
     */
    private function evalTask(array &$taskEvaluationDTOs, Task $task)
    {
        $taskEvaluationDTO = $this->evalBaseTask($task);
        if ($taskEvaluationDTO) {
            $taskEvaluationDTOs[] = $taskEvaluationDTO;
        }

        foreach ($task->childTasks as $childTask) {
            $this->evalTask($taskEvaluationDTOs, $childTask);
        }
    }

    private function evalBaseTask(Task $task): ?TaskEvaluationDTO
    {
        $reports = $this->getAllReportsFromTask($task);

        if (!$reports) {
            return null;
        }

        // Calcuation
        $firstReport = $this->getFirstReportByDate($reports);
        $lastReport = $this->getLastReportByDate($reports);
        $actual = $this->calculateActualFromReports($reports);
        $target = $this->calculateTargetFromReports($reports);

        // Calculate Target
        $targetPriceMin = 0;
        $targetPriceMax = 0;
        $targetPrice = $target['total'];
        if ($task->target) {
            $targetPriceMin = $task->target->value->startDuration->minutes / 60 * self::PRICE_BASE_EXTERNAL;
            $targetPriceMax = $task->target->value->endDuration->minutes / 60 * self::PRICE_BASE_EXTERNAL;
            $targetPrice = $this->clamp($target['total'], $targetPriceMin, $targetPriceMax);
            //$targetPrice = min($targetPriceMax, max($targetPriceMin, $target['total']));
        }
        $contributionMargin = $targetPrice - $actual['total'];
        //$targetMin = $task->target->value

        $taskEvaluationDTO = new TaskEvaluationDTO(
            $task,
            $firstReport,
            $lastReport,
            $actual,
            $target,
            $targetPrice,
            $targetPriceMin,
            $targetPriceMax,
            $contributionMargin
        );

        return $taskEvaluationDTO;
    }

    private function renderTaskEvaluationDTO(TaskEvaluationDTO $taskEvaluationDTO): string
    {
        $task = $taskEvaluationDTO->getTask();
        $firstReport = $taskEvaluationDTO->getFirstReport();
        $lastReport = $taskEvaluationDTO->getLastReport();
        $actual = $taskEvaluationDTO->getActual();
        $target = $taskEvaluationDTO->getTarget();
        $targetPrice = $this->formatCurrency($taskEvaluationDTO->getTargetPrice());
        $targetPriceMin = $this->formatCurrency($taskEvaluationDTO->getTargetPriceMin());
        $targetPriceMax = $this->formatCurrency($taskEvaluationDTO->getTargetPriceMax());
        $contributionMargin = $this->formatCurrency($taskEvaluationDTO->getContributionMargin());

        $taskName = $task->name;
        $firstReportDate = $firstReport->date;
        $lastReportDate = $lastReport->date;
        $actualTotalPrice = $this->formatCurrency($actual['total']);
        $targetTotalPrice = $this->formatCurrency($target['total']);

        // Print
        $string = "Aufgabe: $taskName ($firstReportDate - $lastReportDate)\n";

        foreach ($actual['condensates'] as $condensate) {
            $actualHours = $this->formatHours($condensate['hours']);
            $internalPrice = $this->formatCurrency($condensate['internalPrice']);
            $totalInternalPrice = $this->formatCurrency($condensate['totalInternalPrice']);

            $string .= "IST-Zeit: $actualHours á $internalPrice = $totalInternalPrice\n";
        }
        $string .= "IST-Ausgaben: $actualTotalPrice\n";

        if ($task->target && $task->target->value) {
            $string .= "SOLL-Einnahmen: $targetPrice ($targetPriceMin bis $targetPriceMax) \n";
        } else {
            foreach ($target['condensates'] as $condensate) {
                $targetHoursRounded = $this->formatHours($condensate['hoursRounded']);
                $externalPrice = $this->formatCurrency($condensate['externalPrice']);
                $totalExternalPriceRounded = $this->formatCurrency($condensate['totalExternalPriceRounded']);

                $string .=
                    "SOLL-Zeit (fiktiv): $targetHoursRounded á $externalPrice = $totalExternalPriceRounded\n";
            }
            $string .= "SOLL-Einnahmen (fiktiv): $targetTotalPrice\n";
        }

        $string .= "Deckungsbeitrag: $contributionMargin\n";
        $string .= "\n";

        return $string;
    }

    /**
     * Berechnet den IST-Wert der angegebenen Reports. Liefert ein Array im
     * folgendem Format zurück:
     * [
     *     'total' => <float> - gesamt IST-WERT in Euro über alle Reports
     *     'condensates' => <array> - ein Array mit allen Condensates
     * ]
     *
     * @param Report[] $reports
     */
    private function calculateActualFromReports(array $reports): array
    {
        $durationReports = $this->filterReportsByDuration($reports);

        $groupedDurationReportsByInternalPrice = $this->groupReportsByInternalPrice(
            $durationReports,
            self::PRICE_BASE_INTERNAL
        );

        $condensates = [];
        foreach ($groupedDurationReportsByInternalPrice as $reports) {
            $condensates[] = $this->condenseDurationReports(
                $reports,
                self::PRICE_BASE_EXTERNAL,
                self::PRICE_BASE_INTERNAL
            );
        }

        $actualPrice = 0;
        foreach ($condensates as $condensate) {
            $actualPrice += $condensate['totalInternalPrice'];
        }

        return [
            'total' => $actualPrice,
            'condensates' => $condensates
        ];
    }

    /**
     * Berechnet den SOLL-Wert der angegebenen Reports. Liefert ein Array im
     * folgendem Format zurück:
     * [
     *     'total' => <float> - gesamt SOLL-WERT in Euro über alle Reports
     *     'condensates' => <array> - ein Array mit allen Condensates
     * ]
     *
     * @param Report[] $reports
     */
    private function calculateTargetFromReports(array $reports): array
    {
        $billableReports = $this->filterReportsByBillable($reports);
        $durationReports = $this->filterReportsByDuration($billableReports);

        $groupedDurationReportsByExternalPrice = $this->groupReportsByExternalPrice(
            $durationReports,
            self::PRICE_BASE_EXTERNAL
        );

        $condensates = [];
        foreach ($groupedDurationReportsByExternalPrice as $reports) {
            $condensates[] = $this->condenseDurationReports(
                $reports,
                self::PRICE_BASE_EXTERNAL,
                self::PRICE_BASE_INTERNAL
            );
        }

        $targetPrice = 0;
        foreach ($condensates as $condensate) {
            $targetPrice += $condensate['totalExternalPriceRounded'];
        }

        return [
            'total' => $targetPrice,
            'condensates' => $condensates
        ];
    }

    /**
     * Deckungsbeitragsberechnung:
     * https://www.textbest.de/magazin/aus-dem-agenturalltag-diese-controlling-kennzahlen-sind-wichtig/
     * Bezahlte Stunden x Stundensatz im KVA – erbrachte Stunden x Kosten pro Stunde = Deckungsbeitrag
     *
     * SOLL Einnahmen - IST Ausgaben = Deckungsbeitrag
     */
    private function createEvaluation(array $tasks): string
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
}

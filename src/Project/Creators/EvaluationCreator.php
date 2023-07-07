<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Project;
use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

/**
 * Deckungsbeitragsberechnung:
 * https://www.textbest.de/magazin/aus-dem-agenturalltag-diese-controlling-kennzahlen-sind-wichtig/
 * Bezahlte Stunden x Stundensatz im KVA (Kostenvoranschlag) – erbrachte Stunden x Kosten pro Stunde = Deckungsbeitrag
 *
 * SOLL Einnahmen - IST Ausgaben = Deckungsbeitrag
 */
class EvaluationCreator extends AbstractCreator
{
    private const PRICE_BASE_EXTERNAL = 80.00;
    private const PRICE_BASE_INTERNAL = 60.00;

    /** @var ReportFilter */
    private $reportFilter;

    /** @var ReportCondensateFactory */
    private $reportCondensateFactory;

    /** @var ReportCondensateAdder */
    private $reportCondensateAdder;

    public function __construct()
    {
        $this->reportFilter = new ReportFilter(new ReportPriceSelector());
        $this->reportCondensateFactory = new ReportCondensateFactory(new ReportPriceSelector());
        $this->reportCondensateAdder = new ReportCondensateAdder();
    }

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
        $targetPrice = $target->getExternalTotalPrice();
        if ($task->target) {
            $targetPriceMin = $task->target->value->startDuration->minutes / 60 * self::PRICE_BASE_EXTERNAL;
            $targetPriceMax = $task->target->value->endDuration->minutes / 60 * self::PRICE_BASE_EXTERNAL;
            $targetPrice = $this->clamp($target->getExternalTotalPrice(), $targetPriceMin, $targetPriceMax);
        }
        $contributionMargin = $targetPrice - $actual->getInternalTotalPrice();

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

    /**
     * Berechnet den IST-Wert der angegebenen Reports
     *
     * @param Report[] $reports
     */
    private function calculateActualFromReports(array $reports): ReportCondensate
    {
        $reportCondensates = $this->reportCondensateFactory->createFromReports(
            $reports,
            self::PRICE_BASE_EXTERNAL,
            self::PRICE_BASE_INTERNAL
        );
        $reportCondensate = $this->reportCondensateAdder->addAll($reportCondensates);
        return $reportCondensate;
    }

    /**
     * Berechnet den IST-Wert der angegebenen Reports
     *
     * @param Report[] $reports
     */
    private function calculateTargetFromReports(array $reports): ReportCondensate
    {
        $billableReports = $this->reportFilter->filterByBillable($reports);
        $reportCondensates = $this->reportCondensateFactory->createFromReports(
            $billableReports,
            self::PRICE_BASE_EXTERNAL,
            self::PRICE_BASE_INTERNAL
        );
        $reportCondensate = $this->reportCondensateAdder->addAll($reportCondensates);
        return $reportCondensate;
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
        $actualTotalPrice = $this->formatCurrency($actual->getInternalTotalPrice());
        $targetTotalPrice = $this->formatCurrency($target->getExternalTotalPrice());

        // Print
        $string = "Aufgabe: $taskName ($firstReportDate - $lastReportDate)\n";

        if ($target->getType() === ReportCondensate::TYPE_DURATION) {
            $interalQuantity = $this->formatHours($actual->getInternalQuantity());
            $internalPrice = $this->formatCurrency($actual->getInternalPrice());
            $internalTotalPrice = $this->formatCurrency($actual->getInternalTotalPrice());

            $string .= "IST-Zeit: $interalQuantity á $internalPrice = $internalTotalPrice\n";
        } elseif ($target->getType() === ReportCondensate::TYPE_QUANTITY) {
            $interalQuantity = $actual->getInternalQuantity();
            $internalPrice = $this->formatCurrency($actual->getInternalPrice());
            $internalTotalPrice = $this->formatCurrency($actual->getInternalTotalPrice());

            $string .= "IST: $interalQuantity x $internalPrice = $internalTotalPrice\n";
        }
        $string .= "IST-Ausgaben: $actualTotalPrice\n";

        if ($task->target && $task->target->value) {
            $string .= "SOLL-Einnahmen: $targetPrice ($targetPriceMin bis $targetPriceMax) \n";
        } else {
            $externalPrice = $this->formatCurrency($target->getExternalPrice());
            $externalTotalPrice = $this->formatCurrency($target->getExternalTotalPrice());

            if ($target->getType() === ReportCondensate::TYPE_DURATION) {
                $externalQuenatity = $this->formatHours($target->getExternalQuantity());
                $string .=
                    "SOLL-Zeit (fiktiv): $externalQuenatity á $externalPrice = $externalTotalPrice\n";
            } elseif ($target->getType() === ReportCondensate::TYPE_QUANTITY) {
                $externalQuenatity = $target->getExternalQuantity();
                $string .=
                    "SOLL (fiktiv): $externalQuenatity x $externalPrice = $externalTotalPrice\n";
            }

            $string .= "SOLL-Einnahmen (fiktiv): $targetTotalPrice\n";
        }

        $string .= "Deckungsbeitrag: $contributionMargin\n";
        $string .= "\n";

        return $string;
    }
}

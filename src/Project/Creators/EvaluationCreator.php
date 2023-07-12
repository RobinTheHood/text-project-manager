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
    private const DEFAULT_PRICE_EXTERNAL = 80.00;
    private const DEFAULT_PRICE_INTERNAL = 30.00;

    /** @var ReportFilter */
    private $reportFilter;

    /** @var ReportCondensateFactory */
    private $reportCondensateFactory;

    /** @var ReportCondensateAdder */
    private $reportCondensateAdder;

    /** @var BillItemsCreatorView */
    private $evaluationCreatorView;

    public function __construct()
    {
        $this->reportFilter = new ReportFilter(new ReportPriceSelector());
        $this->reportCondensateFactory = new ReportCondensateFactory(new ReportPriceSelector());
        $this->reportCondensateAdder = new ReportCondensateAdder();
        $this->evaluationCreatorView = new EvaluationCreatorView(
            self::DEFAULT_PRICE_EXTERNAL,
            self::DEFAULT_PRICE_INTERNAL
        );
    }

    public function create(Project $project): string
    {
        $taskEvaluationDTOs = [];
        foreach ($project->tasks as $task) {
            $this->evalTask($taskEvaluationDTOs, $task);
        }

        return $this->evaluationCreatorView->render($taskEvaluationDTOs);
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
            $targetPriceMin = $task->target->value->startDuration->minutes / 60 * self::DEFAULT_PRICE_EXTERNAL;
            $targetPriceMax = $task->target->value->endDuration->minutes / 60 * self::DEFAULT_PRICE_EXTERNAL;
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
            self::DEFAULT_PRICE_EXTERNAL,
            self::DEFAULT_PRICE_INTERNAL
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
            self::DEFAULT_PRICE_EXTERNAL,
            self::DEFAULT_PRICE_INTERNAL
        );
        if (!$reportCondensates) {
            $reportCondensates = [
                $this->reportCondensateFactory->createEmpty()
            ];
        }
        $reportCondensate = $this->reportCondensateAdder->addAll($reportCondensates);
        return $reportCondensate;
    }
}

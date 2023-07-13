<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Entities\DurationRange;
use RobinTheHood\TextProjectManager\Project\Entities\Money;
use RobinTheHood\TextProjectManager\Project\Entities\MoneyRange;
use RobinTheHood\TextProjectManager\Project\Entities\Project;
use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

class BillItemsCreator extends AbstractCreator
{
    private const DEFAULT_PRICE_EXTERNAL = 80.00;
    private const DEFAULT_PRICE_INTERNAL = 30.00;

    /** @var float */
    public $total = 0.0;

    /** @var ReportGrouper */
    private $reportGrouper;

    /** @var ReportFilter */
    private $reportFilter;

    /** @var ReportCondensateFactory */
    private $reportCondensateFactory;

    /** @var ReportCondensateAdder */
    private $reportCondensateAdder;

    /** @var BillItemsCreatorView */
    private $billItemsCreatorView;

    public function __construct()
    {
        $this->reportGrouper = new ReportGrouper(new ReportPriceSelector());
        $this->reportFilter = new ReportFilter(new ReportPriceSelector());
        $this->reportCondensateFactory = new ReportCondensateFactory(new ReportPriceSelector());
        $this->reportCondensateAdder = new ReportCondensateAdder();
        $this->billItemsCreatorView = new BillItemsCreatorView(
            self::DEFAULT_PRICE_EXTERNAL,
            self::DEFAULT_PRICE_INTERNAL
        );
    }

    public function create(Project $project): string
    {
        $billItemDTOs = [];
        foreach ($project->tasks as $task) {
            $this->evalTask($billItemDTOs, $task);
        }

        foreach ($billItemDTOs as $billItemDTO) {
            $this->total += $billItemDTO->getTotalPrice();
        }

        return $this->billItemsCreatorView->render($this->total, $billItemDTOs);
    }

    /**
     * @param BillItemDTO[] $billItemDTOs
     * @param Task $task
     */
    private function evalTask(array &$billItemDTOs, Task $task)
    {
        $billItemDTO = $this->evalBaseTask($task);
        if ($billItemDTO) {
            $billItemDTOs[] = $billItemDTO;
        }

        foreach ($task->childTasks as $childTask) {
            $this->evalTask($billItemDTOs, $childTask);
        }
    }

    private function evalBaseTask(Task $task): ?BillItemDTO
    {
        $reports = $this->getAllReportsFromTask($task);
        $reports = $this->reportFilter->filterByBillable($reports);

        if (!$reports) {
            return null;
        }

        $firstReport = $this->getFirstReportByDate($reports);
        $lastReport = $this->getLastReportByDate($reports);

        $targetHours = 0;
        $targetPrice = 0;
        if ($task->target) {
            $durationReports = $this->reportFilter->filterByDuration($reports);
            $durationReportCondensates = $this->reportCondensateFactory->createFromReports(
                $durationReports,
                self::DEFAULT_PRICE_EXTERNAL,
                self::DEFAULT_PRICE_INTERNAL
            );
            $durationReportCondensate = $this->reportCondensateAdder->addAll($durationReportCondensates);
            $actualHoursRounde = $durationReportCondensate->getExternalQuantity();

            $reportCondensates = $this->reportCondensateFactory->createFromReports(
                $reports,
                self::DEFAULT_PRICE_EXTERNAL,
                self::DEFAULT_PRICE_INTERNAL
            );
            $reportCondensate = $this->reportCondensateAdder->addAll($reportCondensates);
            $actualPrice = $reportCondensate->getExternalTotalPrice();

            if ($task->target->value instanceof Duration) {
                $targetHoursMin = $task->target->value->minutes / 60;
                $targetHoursMax = $task->target->value->minutes / 60;

                $targetHours = $this->clamp($actualHoursRounde, $targetHoursMin, $targetHoursMax);
                $targetPrice = $targetHours  * self::DEFAULT_PRICE_EXTERNAL;
            } elseif ($task->target->value instanceof DurationRange) {
                $targetHoursMin = $task->target->value->startDuration->minutes / 60;
                $targetHoursMax = $task->target->value->endDuration->minutes  / 60;

                $targetHours = $this->clamp($actualHoursRounde, $targetHoursMin, $targetHoursMax);
                $targetPrice = $targetHours  * self::DEFAULT_PRICE_EXTERNAL;
            } elseif ($task->target->value instanceof Money) {
                $targetPriceMin = $task->target->value->value;
                $targetPriceMax = $task->target->value->value;

                $targetPrice = $this->clamp($actualPrice, $targetPriceMin, $targetPriceMax);
            } elseif ($task->target->value instanceof MoneyRange) {
                $targetPriceMin = $task->target->value->startMoney->value;
                $targetPriceMax = $task->target->value->endMoney->value;

                $targetPrice = $this->clamp($actualPrice, $targetPriceMin, $targetPriceMax);
            }
        }

        $durationReportCondensates = $this->processDurationReports($reports);
        $quantityReportCondensates = $this->processQuantityReports($reports);

        return new BillItemDTO(
            $task,
            $firstReport,
            $lastReport,
            $targetHours,
            $targetPrice,
            $durationReportCondensates,
            $quantityReportCondensates
        );
    }

    /**
     * @param Report[] $reports
     *
     * @return ReportCondensate[]
     */
    private function processDurationReports(array $reports): array
    {
        $durationReports = $this->reportFilter->filterByDuration($reports);
        if (!$durationReports) {
            return [];
        }

        $resultDurationCondensates = [];
        $groupedDurationReports = $this->reportGrouper->groupByExternalPrice(
            $durationReports,
            self::DEFAULT_PRICE_EXTERNAL
        );
        foreach ($groupedDurationReports as $durationReports) {
            $durationReportCondensates = $this->reportCondensateFactory->createFromReports(
                $durationReports,
                self::DEFAULT_PRICE_EXTERNAL,
                self::DEFAULT_PRICE_INTERNAL
            );
            $durationReportCondensate = $this->reportCondensateAdder->addAll($durationReportCondensates);
            $resultDurationCondensates[] = $durationReportCondensate;
        }
        return $resultDurationCondensates;
    }

    /**
     * @param Report[] $reports
     *
     * @return ReportCondensate[]
     */
    private function processQuantityReports(array $reports): array
    {
        $quantityReports = $this->reportFilter->filterByQuantity($reports);
        if (!$quantityReports) {
            return [];
        }

        $quantityReportCondensates = $this->reportCondensateFactory->createFromReports(
            $quantityReports,
            self::DEFAULT_PRICE_EXTERNAL,
            self::DEFAULT_PRICE_INTERNAL
        );
        $quantityReportCondensate = $this->reportCondensateAdder->addAll($quantityReportCondensates);
        return [$quantityReportCondensate];
    }
}

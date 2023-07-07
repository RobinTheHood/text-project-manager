<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Description;
use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Entities\DurationRange;
use RobinTheHood\TextProjectManager\Project\Entities\Money;
use RobinTheHood\TextProjectManager\Project\Entities\MoneyRange;
use RobinTheHood\TextProjectManager\Project\Entities\Project;
use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Entities\Task;

class BillItemsCreator extends AbstractCreator
{
    private const PRICE_BASE_EXTERNAL = 80.00;
    private const PRICE_BASE_INTERNAL = 60.00;

    public $total = 0.0;

    /** @var ReportGrouper */
    private $reportGrouper;

    /** @var ReportFilter */
    private $reportFilter;

    /** @var ReportCondensateFactory */
    private $reportCondensateFactory;

    /** @var ReportCondensateAdder */
    private $reportCondensateAdder;

    public function __construct()
    {
        $this->reportGrouper = new ReportGrouper(new ReportPriceSelector());
        $this->reportFilter = new ReportFilter(new ReportPriceSelector());
        $this->reportCondensateFactory = new ReportCondensateFactory(new ReportPriceSelector());
        $this->reportCondensateAdder = new ReportCondensateAdder();
    }

    public function create(Project $project): string
    {
        $billItemDTOs = [];
        foreach ($project->tasks as $task) {
            $this->evalTask($billItemDTOs, $task);
        }

        $total = 0;
        $string = '';
        //$string .= "####### RECHNUNGSPOSITIONEN #######\n";
        foreach ($billItemDTOs as $billItemDTO) {
            $string .= $this->renderBillItemDTO($billItemDTO);
            $billItemDTOTotal = $this->getTotalFromBillItemDTO($billItemDTO);
            $total += $billItemDTOTotal;
            $string .= "Gesamt: {$this->formatCurrency($billItemDTOTotal)}\n";
            $string .= "\n";
        }

        $string .= "Gesamt: " . $this->formatCurrency($total) . "\n";

        return $string;
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
                self::PRICE_BASE_EXTERNAL,
                self::PRICE_BASE_INTERNAL
            );
            $durationReportCondensate = $this->reportCondensateAdder->addAll($durationReportCondensates);
            $actualHoursRounde = $durationReportCondensate->getExternalQuantity();

            $reportCondensates = $this->reportCondensateFactory->createFromReports(
                $reports,
                self::PRICE_BASE_EXTERNAL,
                self::PRICE_BASE_INTERNAL
            );
            $reportCondensate = $this->reportCondensateAdder->addAll($reportCondensates);
            $actualPrice = $reportCondensate->getExternalTotalPrice();

            if ($task->target->value instanceof Duration) {
                $targetHoursMin = $task->target->value->minutes / 60;
                $targetHoursMax = $task->target->value->minutes / 60;

                $targetHours = $this->clamp($actualHoursRounde, $targetHoursMin, $targetHoursMax);
                $targetPrice = $targetHours  * self::PRICE_BASE_EXTERNAL;
            } elseif ($task->target->value instanceof DurationRange) {
                $targetHoursMin = $task->target->value->startDuration->minutes / 60;
                $targetHoursMax = $task->target->value->endDuration->minutes  / 60;

                $targetHours = $this->clamp($actualHoursRounde, $targetHoursMin, $targetHoursMax);
                $targetPrice = $targetHours  * self::PRICE_BASE_EXTERNAL;
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
            self::PRICE_BASE_EXTERNAL
        );
        foreach ($groupedDurationReports as $durationReports) {
            $durationReportCondensates = $this->reportCondensateFactory->createFromReports(
                $durationReports,
                self::PRICE_BASE_EXTERNAL,
                self::PRICE_BASE_INTERNAL
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
            self::PRICE_BASE_EXTERNAL,
            self::PRICE_BASE_INTERNAL
        );
        $quantityReportCondensate = $this->reportCondensateAdder->addAll($quantityReportCondensates);
        return [$quantityReportCondensate];
    }

    private function getTotalFromBillItemDTO(BillItemDTO $billItemDTO): float
    {
        $task = $billItemDTO->getTask();

        if ($task->target) {
            return $billItemDTO->getTotalPrice();
        } else {
            $total = 0;

            foreach ($billItemDTO->getDurationReportCondensates() as $reportCondensate) {
                $total += $reportCondensate->getExternalTotalPrice();
            }

            foreach ($billItemDTO->getQuantityReportCondensates() as $reportCondensate) {
                $total += $reportCondensate->getExternalTotalPrice();
            }

            return $total;
        }
    }

    private function renderBillItemDTO(BillItemDTO $billItemDTO): string
    {
        $string = 0;

        $task = $billItemDTO->getTask();
        $firstReport = $billItemDTO->getFirstReport();
        $lastReport = $billItemDTO->getLastReport();

        $dateRange = "{$firstReport->date} - {$lastReport->date}";
        if ($this->compareDate($firstReport->date, $lastReport->date) === 0) {
            $dateRange = "{$firstReport->date}";
        }

        $string = "{$task->name} ({$dateRange})\n";
        if ($task->description && $task->description->type === Description::TYPE_VISABLE) {
            $string .= $task->description->value . "\n";
        }

        if ($task->target) {
            $formatedTotalExternalPrice = $this->formatCurrency($billItemDTO->getTotalPrice());

            if ($billItemDTO->getTargetHours()) {
                $formatedHours = $this->formatHours($billItemDTO->getTargetHours());
                $formatedExternalPrice = $this->formatCurrency(self::PRICE_BASE_EXTERNAL);
                $string .= "$formatedHours á $formatedExternalPrice = $formatedTotalExternalPrice\n";
            } else {
                $string .= "$formatedTotalExternalPrice\n";
            }
        } else {
            $string .= $this->renderDurationReports($billItemDTO->getDurationReportCondensates());
            $string .= $this->renderQuantityReports($billItemDTO->getQuantityReportCondensates());
        }

        return $string;
    }

    /**
     * @param ReportCondensate[] $reportCondensate
     */
    private function renderDurationReports(array $reportCondensates): string
    {
        $string = '';

        /** @var ReportCondensate */
        foreach ($reportCondensates as $reportCondensate) {
            $formatedHours = $this->formatHours($reportCondensate->getExternalQuantity());
            $formatedExternalPrice = $this->formatCurrency($reportCondensate->getExternalPrice());
            $formatedTotalExternalPrice = $this->formatCurrency($reportCondensate->getExternalTotalPrice());

            $string .= "$formatedHours á $formatedExternalPrice = $formatedTotalExternalPrice\n";
        }
        return $string;
    }

    /**
     * @param ReportCondensate[] $reportCondensate
     */
    private function renderQuantityReports(array $reportCondensates): string
    {
        $string = '';

        /** @var ReportCondensate */
        foreach ($reportCondensates as $reportCondensate) {
            $string = "{$this->formatCurrency($reportCondensate->getExternalTotalPrice())}\n";
        }

        return $string;
    }
}

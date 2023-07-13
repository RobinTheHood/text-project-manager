<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use RobinTheHood\TextProjectManager\Project\Entities\Description;

class BillItemsCreatorView extends AbstractView
{
    /** @var float */
    private $externalPrice;

    /** @var float */
    private $internalPrice;

    public function __construct(float $externalPrice, float $internalPrice)
    {
        $this->externalPrice = $externalPrice;
        $this->internalPrice = $internalPrice;
    }

    /**
     * @param BillItemDTO[] $billItemDTOs
     */
    public function render(float $total, array $billItemDTOs): string
    {

        $total = 0;
        $string = '';
        $string .= "####### RECHNUNGSPOSITIONEN #######\n";
        foreach ($billItemDTOs as $billItemDTO) {
            $string .= $this->renderBillItemDTO($billItemDTO);
            $billItemDTOTotal = $billItemDTO->getTotalPrice();
            $total += $billItemDTOTotal;
            $string .= "Gesamt: {$this->formatCurrency($billItemDTOTotal)}\n";
            $string .= "\n";
        }

        $string .= "Gesamt: " . $this->formatCurrency($total) . "\n";

        return $string;
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
                $formatedExternalPrice = $this->formatCurrency($this->externalPrice);
                $string .= "$formatedHours à $formatedExternalPrice = $formatedTotalExternalPrice\n";
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

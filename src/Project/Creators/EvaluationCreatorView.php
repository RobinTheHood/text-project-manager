<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

class EvaluationCreatorView extends AbstractView
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
     * @param TaskEvaluationDTO[] $taskEvaluationDTOs
     */
    public function render(float $total, array $taskEvaluationDTOs)
    {
        $externalPriceFormated = $this->formatCurrency($this->externalPrice);
        $internalPriceFormated = $this->formatCurrency($this->internalPrice);

        $string = '';
        $string .= "####### AUSWERTUNG #######\n";
        $string .= "Link: https://www.textbest.de/magazin/aus-dem-agenturalltag-diese-controlling-kennzahlen-sind-wichtig/\n";
        $string .= "\n";
        $string .= "### Basiswerte ###\n";
        $string .= "$internalPriceFormated interner Stundensatz (Agentur-Kosten pro Stunde, Büro, Strom, Personal, ...)\n";
        $string .= "$externalPriceFormated externer Stundensatz (Kunden-Kosten pro Stunde)\n";
        $string .= "\n";

        /** @var TaskEvaluationDTO $taskEvaluationDTO*/
        foreach ($taskEvaluationDTOs as $taskEvaluationDTO) {
            $string .= $this->renderTaskEvaluationDTO($taskEvaluationDTO);
        }

        $string .= "### Gesamter Auftrag ### \n";
        $string .= "{$this->formatCurrency($total)} Deckungsbeitrag\n";

        return $string;
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

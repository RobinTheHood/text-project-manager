<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

class BillItemsCreator
{
    public function create(array $tasks): string
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
}

<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Entities\Project;

class ProjectParser
{
    private $lineNumber = 0;

    public function start(): Project
    {
        $fileContent = file_get_contents(__DIR__ . '/data/ProjectPlan01.txt');
        return $this->parse($fileContent);
    }

    public function parse(string $string): Project
    {
        $timeParser = new TimeParser();
        $timeRangeParser = new TimeRangeParser($timeParser);

        $moneyParser = new MoneyParser();
        $moneyRangeParser = new MoneyRangeParser($moneyParser);

        $targetParser = new TargetParser($timeParser, $timeRangeParser, $moneyParser, $moneyRangeParser);
        $taskParser = new TaskParser($targetParser);

        $amountParser = new AmountParser($timeParser);
        $reportParser = new ReportParser($amountParser, $moneyParser);

        /**
         * @var Task[]
         */
        $tasks = [];

        /**
         * @var Task
         */
        $currentTask = null;

        $lines = explode("\n", $string);
        foreach ($lines as $line) {
            $this->lineNumber++;

            $task = $taskParser->parse($line);
            if ($task) {
                if ($currentTask) {
                    $tasks[] = $currentTask;
                }

                $currentTask = $task;
                continue;
            }

            $report = $reportParser->parse($line);
            if ($report && $currentTask) {
                $currentTask->reports[] = $report;
            }
        }

        if ($currentTask) {
            $tasks[] = $currentTask;
        }

        $project = new Project();
        $project->tasks = $tasks;

        return $project;
    }
}

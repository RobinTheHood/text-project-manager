<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Project\Entities\Project;

class ProjectParser
{
    public const SUBTASK_STATUS_BILLABLE = 1;
    public const SUBTASK_STATUS_UNBILLABLE = 2;

    private $lineNumber = 0;

    public function start(): Project
    {
        $fileContent = file_get_contents(__DIR__ . '/data/ProjectPlan01.txt');
        return $this->parse($fileContent);
    }

    public function parse($string): Project
    {
        $timeParser = new TimeParser();
        $targetParser = new TargetParser($timeParser);
        $taskParser = new TaskParser($targetParser);

        $amountParser = new AmountParser();
        $priceParser = new PriceParser();
        $reportParser = new ReportParser($amountParser, $priceParser);

        $fileContent = file_get_contents(__DIR__ . '/data/ProjectPlan01.txt');
        $lines = explode("\n", $fileContent);

        $tasks = [];

        /**
         * @var Task
         */
        $currentTask = null;

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

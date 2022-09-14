<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use RobinTheHood\TextProjectManager\Project\Entities\Project;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class ProjectParser
{
    /**
     * <project> ::= <task_list>
     */
    public function parse(Parser $parser): ?Project
    {
        $project = new Project();
        if ($tasks = $this->parseTaskList($parser)) {
            $project->tasks = $tasks;
        }
        return $project;
    }

    /**
     * <task_list> ::= (task)*
     */
    private function parseTaskList(Parser $parser): array
    {
        $tasks = [];
        while ($task = (new TaskParser())->parse($parser)) {
            $tasks[] = $task;
        }
        return $tasks;
    }
}

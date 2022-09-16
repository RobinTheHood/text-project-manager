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

        if (!$parser->accept(Token::TYPE_EOF)) {
            $parser->throwException('Unexpected token after task list');
            //throw new Exception("Unexpected token after task list on line" . $parser->getLookaheadToken()->getLine(), );
        }
        return $project;
    }

    /**
     * <task_list> ::= (task)*
     */
    private function parseTaskList(Parser $parser): array
    {
        $tasks = [];
        $taskParser = new TaskParser();
        $taskParser->setLevel(0);
        while (!$parser->isEndOfFile() && $task = $taskParser->parse($parser)) {
            $tasks[] = $task;
        }
        return $tasks;
    }
}

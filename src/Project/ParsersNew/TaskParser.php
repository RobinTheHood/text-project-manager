<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use Exception;
use RobinTheHood\TextProjectManager\Project\Entities\Target;
use RobinTheHood\TextProjectManager\Project\Entities\Task;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class TaskParser
{
    /**
     * @var Task
     */
    private $task;

    /**
     * <task> ::= <token_1> | <token_task_2>
     */
    public function parse(Parser $parser): ?Task
    {
        $this->task = new Task();
        $result = $this->parseTask1($parser);

        if (!$result) {
            $result = $this->parseTask2($parser);
        }

        if (!$result) {
            return null;
        }

        return $this->task;
    }

    /**
     * <task_1> ::= <token_task_start> <task_header> <task_body> <new_lines>
     */
    private function parseTask1(Parser $parser): bool
    {
        if (!$token = $parser->accept(Token::TYPE_TASK_START)) {
            return false;
        }

        if (!$taskHeader = (new TaskHeaderParser())->parse($parser)) {
            return false;
        }
        $this->task->name = $taskHeader['name'];
        $this->task->target = $taskHeader['target'];

        if (!$reports = $this->parseBody($parser)) {
            return false;
        }

        (new NewLinesParser())->parse($parser);

        $this->task->reports = $reports;

        return true;
    }

    /**
     * <task_2> ::= <token_task_start> <task_header> <new_lines>
     */
    private function parseTask2(Parser $parser): bool
    {
        if (!$token = $parser->accept(Token::TYPE_TASK_START)) {
            return false;
        }

        if (!$taskHeader = (new TaskHeaderParser())->parse($parser)) {
            return false;
        }

        (new NewLinesParser())->parse($parser);

        $this->task->name = $taskHeader['name'];
        $this->task->target = $taskHeader['target'];

        return true;
    }


    /**
     * <task_body> ::= <report_list>
     */
    private function parseBody(Parser $parser): array
    {
        $reports = $this->parseReportList($parser);
        return $reports;
    }

    /**
     * <report_list> ::= (<report>)*
     */
    private function parseReportList(Parser $parser): array
    {
        $reports = [];
        $reportParser = new ReportParser();
        while ($report = $reportParser->parse($parser)) {
            $reports[] = $report;
        }
        return $reports;
    }
}

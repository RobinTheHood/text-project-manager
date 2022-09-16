<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use RobinTheHood\TextProjectManager\Project\Entities\Description;
use RobinTheHood\TextProjectManager\Project\Entities\Task;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class TaskParser
{
    /**
     * @var int
     */
    private $level = 0;

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * <task> ::= <token_task_start> <task_header> <task_body> <new_lines>
     */
    public function parse(Parser $parser): ?Task
    {
        $task = new Task();
        if (!$this->parseTaskStart($parser)) {
            return null;
        }

        $taskHeader = (new TaskHeaderParser())->parse($parser);

        $task->name = $taskHeader['name'];
        $task->target = $taskHeader['target'];

        $body = $this->parseBody($parser);

        $task->description = $body['description'];
        $task->users = $body['users'];
        $task->childTasks = $body['childTasks'];


        (new NewLinesParser())->parse($parser);

        return $task;
    }

    private function parseTaskStart(Parser $parser): ?Token
    {
        $levelString = '#';
        if ($this->level === 1) {
            $levelString = '##';
        } elseif ($this->level === 2) {
            $levelString = '###';
        }

        return $parser->accept(Token::TYPE_TASK_START, $levelString);
    }

    /**
     * <task_body> ::= <description> <user_list> <subtask_list> | <user_list> <subtask_list>
     */
    private function parseBody(Parser $parser): array
    {
        $body = [
            'description' => null,
            'users' => [],
            'childTasks' => []
        ];

        $body['description'] = $this->parseTaskDescription($parser);

        $users = $this->parseUserList($parser);
        if ($users) {
            $body['users'] = $users;
        }

        $subTasks = $this->parseChildTaskList($parser);
        if ($subTasks) {
            $body['childTasks'] = $subTasks;
        }

        return $body;
    }

    private function parseTaskDescription(Parser $parser): ?Description
    {
        if (!$token = $parser->accept(Token::TYPE_STRING)) {
            return null;
        }

        if (!$parser->acceptNewlineOrEndOfFile()) {
            $parser->throwException('Missing new line after user header');
        }

        (new NewLinesParser())->parse($parser);

        $description = new Description();
        if (strpos($token->string, '!') === 0) {
            $description->type = Description::TYPE_VISABLE;
            $description->value = trim(substr($token->string, 1, strlen($token->string) - 1));
        } else {
            $description->type = Description::TYPE_HIDDEN;
            $description->value = $token->string;
        }

        return $description;
    }

    /**
     * <subtask_list> ::= (<sub_task>)*
     */
    private function parseChildTaskList(Parser $parser): array
    {
        $childTasks = [];
        $taskParser = new TaskParser();
        $taskParser->setLevel($this->level + 1);

        while (!$parser->isEndOfFile() && $childTask = $taskParser->parse($parser)) {
            $childTasks[] = $childTask;
        }
        return $childTasks;
    }

    /**
     * <user_list> ::= (<user>)*
     */
    private function parseUserList(Parser $parser): array
    {
        $users = [];
        $usersParser = new UserParser();
        while (!$parser->isEndOfFile() && $user = $usersParser->parse($parser)) {
            $users[] = $user;
        }
        return $users;
    }
}

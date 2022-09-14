<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use Exception;
use RobinTheHood\TextProjectManager\Project\Entities\Target;
use RobinTheHood\TextProjectManager\Project\Entities\Task;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class TaskHeaderParser
{
    /**
     * <task_header> ::= <task_header_1> | <task_header_2>
     */
    public function parse(Parser $parser): array
    {
        $result = $this->parseHeader1($parser);

        if (!$result) {
            $result = $this->parseHeader2($parser);
        }

        return $result;
    }

    /**
     * <task_header_1> ::= <token_string> <token_sparator> <target> <token_new_line>
     * <target> ::= <duration_range> | <money_range> | <duration> | <money>
     * <duration_range> :: <duration> - <duration>
     * <money_range> ::= <money> - <money>
     */
    private function parseHeader1(Parser $parser): array
    {
        $taskHeader = [];

        if (!$token = $parser->accept(Token::TYPE_STRING)) {
            return [];
        }
        $taskHeader['name'] = $token->string;

        if (!$token = $parser->accept(Token::TYPE_SEPARATOR)) {
            return [];
        }

        if (!$duration = (new DurationParser())->parse($parser)) {
            return [];
        }
        $target = new Target();
        $target->value = $duration;
        $target->type = Target::TYPE_TIME;

        if (!$token = $parser->accept(Token::TYPE_NEW_LINE)) {
            return [];
        }

        $taskHeader['target'] = $target;

        return $taskHeader;
    }

    /**
     * <task_header_2> ::= <token_string> <token_new_line>
     */
    private function parseHeader2(Parser $parser): array
    {
        $taskHeader = [];

        if (!$token = $parser->accept(Token::TYPE_STRING)) {
            return [];
        }
        $taskHeader['name'] = $token->string;

        if (!$token = $parser->accept(Token::TYPE_NEW_LINE)) {
            return [];
        }

        return $taskHeader;
    }
}

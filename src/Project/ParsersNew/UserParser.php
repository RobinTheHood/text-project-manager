<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use RobinTheHood\TextProjectManager\Project\Entities\User;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class UserParser
{
    /**
     * <user> ::= <token_user_start> <token_type_user_name> <new_lines> <report_list>
     */
    public function parse(Parser $parser): ?User
    {
        $user = new User();
        if (!$token = $parser->accept(Token::TYPE_USER_START)) {
            return null;
        }

        if (!$token = $parser->accept(Token::TYPE_USER_NAME)) {
            $parser->throwException('Expect user name after user start');
        }
        $user->name = trim($token->string);

        if (!$parser->acceptNewlineOrEndOfFile()) {
            $parser->throwException('Missing new line after user header');
        }

        $user->repors = $this->parseReportList($parser);

        (new NewLinesParser())->parse($parser);

        return $user;
    }

    /**
     * <report_list> ::= (<report>)*
     */
    private function parseReportList(Parser $parser): array
    {
        $reports = [];
        $reportParser = new ReportParser();
        while (!$parser->isEndOfFile() && $report = $reportParser->parse($parser)) {
            $reports[] = $report;
        }
        return $reports;
    }
}

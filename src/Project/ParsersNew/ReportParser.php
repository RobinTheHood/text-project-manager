<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use RobinTheHood\TextProjectManager\Project\Entities\Report;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class ReportParser
{
    /**
     * <report> ::= <token_report_start> <token_date>; <amount>; <token_string> <token_new_line> <new_lines>
     * <amount> ::= <duration>
     */
    public function parse(Parser $parser): ?Report
    {
        $report = new Report();
        if (!$token = $parser->accept(Token::TYPE_REPORT_START)) {
            return null;
        }

        if (!$token = $parser->accept(Token::TYPE_DATE)) {
            return null;
        }
        $report->date = $token->string;

        if (!$token = $parser->accept(Token::TYPE_SEPARATOR)) {
            return null;
        }

        if (!$duration = (new DurationParser())->parse($parser)) {
            return null;
        }
        $report->amount = $duration;

        if (!$token = $parser->accept(Token::TYPE_SEPARATOR)) {
            return null;
        }

        if (!$token = $parser->accept(Token::TYPE_STRING)) {
            return null;
        }
        $report->description = trim($token->string);

        if (!$token = $parser->accept(Token::TYPE_NEW_LINE)) {
            return null;
        }

        (new NewLinesParser())->parse($parser);

        return $report;
    }

    private function parseNewLines(Parser $parser): void
    {
        while ($token = $parser->accept(Token::TYPE_NEW_LINE));
    }
}

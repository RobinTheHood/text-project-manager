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

        $report->type = Report::TYPE_BILLABLE;
        if ($token->string === '-') {
            $report->type = Report::TYPE_UNBILLABLE;
        }

        if (!$token = $parser->accept(Token::TYPE_DATE)) {
            $parser->throwException('Expect date after report start');
        }
        $report->date = $token->string;

        if (!$token = $parser->accept(Token::TYPE_SEPARATOR)) {
            $parser->throwException('Expect separator after report date');
        }

        if (!$number = (new NumberParser())->parse($parser)) {
            $parser->throwException('Expect amount (duration or quantity) after report date');
        }

        $amount = $number->contertNumberToAmount();
        if (!$amount) {
            $parser->throwException('Expect amount (duration or quantity) after report date');
        }
        $report->amount = $amount;

        if (!$token = $parser->accept(Token::TYPE_SEPARATOR)) {
            $parser->throwException('Expect separator after report duration');
        }

        if (!$token = $parser->accept(Token::TYPE_STRING)) {
            $parser->throwException('Expect report title after report duration');
        }
        $report->description = trim($token->string);

        if ($token = $parser->accept(Token::TYPE_SEPARATOR)) {
            $number = (new NumberParser())->parse($parser);
            if (!$number || !($money = $number->convertToMoney())) {
                $parser->throwException('Expect report externalprice after report title');
            }
            $report->externalPrice = $money;
        }

        if ($token = $parser->accept(Token::TYPE_SEPARATOR)) {
            $number = (new NumberParser())->parse($parser);
            if (!$number || !($money = $number->convertToMoney())) {
                $parser->throwException('Expect report internal price after report external price');
            }
            $report->internalPrice = $money;
        }


        if (!$parser->acceptNewlineOrEndOfFile()) {
            $parser->throwException('Missing new line after task header');
        }

        (new NewLinesParser())->parse($parser);

        return $report;
    }
}

<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use RobinTheHood\TextProjectManager\Project\Entities\DurationRange;
use RobinTheHood\TextProjectManager\Project\Entities\MoneyRange;
use RobinTheHood\TextProjectManager\Project\Entities\Target;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class TaskHeaderParser
{
    /**
     * <task_header> ::= <token_string> <option_target> <token_new_line>
     */
    public function parse(Parser $parser): array
    {
        $taskHeader = [
            'name' => '',
            'target' => null
        ];

        if (!$token = $parser->accept(Token::TYPE_STRING)) {
            $parser->throwException('Missing task title');
        }
        $taskHeader['name'] = trim($token->string);
        $taskHeader['target'] = $this->parseOptionTarget($parser);

        if (!$parser->acceptNewlineOrEndOfFile()) {
            $parser->throwException('Missing new line after task header');
        }

        (new NewLinesParser())->parse($parser);

        return $taskHeader;
    }

    /**
     * <option_target> ::= <token_sparator> (<duration> | <duration_range> | <money> | <money_range>) | null
     */
    private function parseOptionTarget(Parser $parser): ?Target
    {
        $target = new Target();

        if (!$parser->accept(Token::TYPE_SEPARATOR)) {
            return null;
        }

        $number = (new NumberParser())->parse($parser);
        if (!$number) {
            $parser->throwException("Expect target (duration, durationRange, money or moneyRange) after first ; in task header.");
        }

        $durationStart = $number->convertToDuration();
        if ($durationStart) {
            if (!$parser->accept(Token::TYPE_SEPARATOR, '-')) {
                $target->value = $durationStart;
                return $target;
            }

            $number = (new NumberParser())->parse($parser);
            if (!$number || !$durationEnd = $number->convertToDuration()) {
                $parser->throwException('Expecting duration after - in task header');
            }

            $durationRange = new DurationRange();
            $durationRange->startDuration = $durationStart;
            $durationRange->endDuration = $durationEnd;

            $target->value = $durationRange;
            return $target;
        }

        $moneyStart = $number->convertToMoney();
        if ($moneyStart) {
            if (!$parser->accept(Token::TYPE_SEPARATOR, '-')) {
                $target->value = $moneyStart;
                return $target;
            }

            $number = (new NumberParser())->parse($parser);
            if (!$number || !$moneyEnd = $number->convertToMoney()) {
                $parser->throwException('Expecting Money after - in task header');
            }

            $moneyRange = new MoneyRange();
            $moneyRange->startMoney = $moneyStart;
            $moneyRange->endMoney = $moneyEnd;

            $target->value = $moneyRange;
            return $target;
        }

        $parser->throwException("Expect target (duration, durationRange, money or moneyRange) after first ; in task header.");
    }
}

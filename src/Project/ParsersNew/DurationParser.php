<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use Exception;
use RobinTheHood\TextProjectManager\Project\Entities\Duration;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class DurationParser
{
    /**
     * <time> ::= <number> | <token_time>
     */
    public function parse(Parser $parser): ?Duration
    {
        $number = (new NumberParser())->parse($parser);
        if ($number) {
            if ($number->unit === '') {
                $minutes = (int) $number->value;
            } elseif ($number->unit === 'min') {
                $minutes = (int) $number->value;
            } elseif ($number->unit === 'h') {
                $minutes = (int) ($number->value * 60);
            } else {
                throw new Exception('Not allowed time unit: ' . $number->unit);
            }
        } elseif ($token = $parser->accept(Token::TYPE_TIME)) {
            $minutes = $this->convertTimeToMinutes($token->string);
        } else {
            return null;
        }

        $time = new Duration();
        $time->minutes = $minutes;
        return $time;
    }

    private function convertTimeToMinutes($string): int
    {
        $parts = explode(':', $string);
        $hours = $parts[0];
        $minutes = $parts[1];
        return $hours * 60 + $minutes;
    }
}

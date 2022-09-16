<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use RobinTheHood\TextProjectManager\Project\Entities\Number;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class NumberParser
{
    /**
     * <number> ::= <token_int> <token_unit> | <token_float> <token_unit> | <token_int> | <token_float>
     */
    public function parse(Parser $parser): ?Number
    {
        $number = new Number();

        if ($token = $parser->accept(Token::TYPE_INT)) {
            $number->value = $this->toFloat($token->string);
            if ($token = $parser->accept(Token::TYPE_UNIT)) {
                $number->unit = $token->string;
            }
            return $number;
        }

        if ($token = $parser->accept(Token::TYPE_FLOAT)) {
            $number->value = $this->toFloat($token->string);
            if ($token = $parser->accept(Token::TYPE_UNIT)) {
                $number->unit = $token->string;
            }
            return $number;
        }

        return null;
    }

    private function toFloat(string $string): float
    {
        $string = str_replace(',', '.', $string);
        return floatval($string);
    }
}

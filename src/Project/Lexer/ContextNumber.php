<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

use Exception;

class ContextNumber implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        $digits = '0123456789';

        if (!$lexer->accept($digits)) {
            throw new Exception('ContextNumber Error 1');
        }
        $lexer->acceptRun($digits);

        if ($lexer->accept('.,')) {
            if (!$lexer->accept($digits)) {
                throw new Exception('ContextNumber Error 1');
            }
            $lexer->acceptRun($digits);
            $lexer->emit(Token::TYPE_FLOAT);
        } elseif ($lexer->accept(':')) {
            if (!$lexer->accept($digits)) {
                throw new Exception('ContextNumber Error 2');
            }
            $lexer->acceptRun($digits);
            $lexer->emit(Token::TYPE_TIME);
        } else {
            $lexer->emit(Token::TYPE_INT);
        }

        if ($lexer->accept(' ')) {
            $lexer->acceptRun(' ');
            $lexer->emit(Token::TYPE_SPACE);
        }

        $letters = "abcdefghijklmnopqrstuvwxyz€";
        if ($lexer->accept($letters)) {
            $lexer->acceptRun($letters);
            $lexer->emit(Token::TYPE_UNIT);
        }

        $lexer->popContext();
    }

    public function __toString()
    {
        return 'CONTEXT_NUMBER';
    }
}

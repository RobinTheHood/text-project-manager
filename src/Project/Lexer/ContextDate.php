<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

use Exception;

class ContextDate implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        if ($lexer->acceptString('heute')) {
            $lexer->emit(Token::TYPE_DATE);
            $lexer->popContext();
            return;
        }

        if ($lexer->acceptString('gestern')) {
            $lexer->emit(Token::TYPE_DATE);
            $lexer->popContext();
            return;
        }

        $digits = "0123456789";
        if (!$lexer->accept($digits)) {
            throw new Exception('ContextDate Error 1');
        }

        $lexer->acceptRun($digits);

        if (!$lexer->accept('.')) {
            throw new Exception('ContextDate Error 2');
        }

        $lexer->acceptRun($digits);

        if (!$lexer->accept('.')) {
            throw new Exception('ContextDate Error 3');
        }

        if (!$lexer->accept($digits)) {
            throw new Exception('ContextDate Error 3');
        }

        $lexer->acceptRun($digits);

        $lexer->emit(Token::TYPE_DATE);
        $lexer->popContext();
    }

    public function __toString()
    {
        return 'CONTEXT_DATE';
    }
}

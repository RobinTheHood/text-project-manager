<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextUnit implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        $letters = "abcdefghijklmnopqrstuvwxyz€";
        $lexer->acceptRun($letters);
        $lexer->emit(Token::TYPE_UNIT);
        $lexer->popContext();
    }

    public function __toString()
    {
        return 'CONTEXT_UNIT';
    }
}

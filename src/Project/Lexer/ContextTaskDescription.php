<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTaskDescription implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        $lexer->acceptNotRun("\n");
        $lexer->emit(Token::TYPE_STRING);

        $lexer->accept("\n");
        $lexer->emit(Token::TYPE_NEW_LINE);
        $lexer->popContext();
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_HEADER_TITLE';
    }
}

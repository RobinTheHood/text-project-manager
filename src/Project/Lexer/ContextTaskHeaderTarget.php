<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTaskHeaderTarget implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        if ($lexer->accept(' ')) {
            $lexer->emit(Token::TYPE_SPACE);
            return;
        }

        if ($lexer->accept('-')) {
            $lexer->emit(Token::TYPE_SEPARATOR);
            return;
        }

        $digits = '0123456789';
        if ($lexer->seek($digits)) {
            $lexer->pushContext(new ContextNumber());
            return;
        }

        $lexer->popContext();
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_HEADER_TARGET';
    }
}

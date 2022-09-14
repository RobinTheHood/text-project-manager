<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTaskHeader implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        if ($lexer->accept('#')) {
            $lexer->acceptRun('#');
            $lexer->emit(Token::TYPE_TASK_START);
            $lexer->pushContext(new ContextTaskHeaderTitle());
            return;
        }

        if ($lexer->accept(';')) {
            $lexer->emit(Token::TYPE_SEPARATOR);
            $lexer->pushContext(new ContextTaskHeaderTarget());
            return;
        }

        $digits = '0123456789';
        if ($lexer->seek($digits)) {
            $lexer->pushContext(new ContextNumber());
            return;
        }

        if ($lexer->accept("\n")) {
            $lexer->popContext();
            $lexer->emit(Token::TYPE_NEW_LINE);
            return;
        }

        $lexer->acceptNotRun(";\n" . $digits);
        $lexer->emit(Token::TYPE_UNKOWN_TOKEN);
        return;
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_HEADER';
    }
}

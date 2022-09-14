<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTask implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        if ($lexer->seek('#')) {
            $lexer->resetContext(new ContextRoot());
            $lexer->pushContext(new ContextTask());
            $lexer->pushContext(new ContextTaskHeader());
            return;
        }

        if ($lexer->accept('@')) {
            $lexer->emit(Token::TYPE_USER_START);
            $lexer->pushContext(new ContextTaskUser());
            return;
        }

        if ($lexer->seek('-+')) {
            $lexer->pushContext(new ContextTaskReport());
            return;
        }

        if ($lexer->accept("\n")) {
            $lexer->emit(Token::TYPE_NEW_LINE);
            return;
        }

        $lexer->acceptNotRun("#@-+\n");
        $lexer->emit(Token::TYPE_STRING);
    }

    public function __toString()
    {
        return 'CONTEXT_TASK';
    }
}

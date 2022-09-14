<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTaskHeaderTitle implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        if ($lexer->seek(";\n")) {
            $lexer->popContext();
            return;
        }

        $lexer->acceptNotRun(";\n");
        $lexer->emit(Token::TYPE_STRING);
        return;
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_HEADER_TITLE';
    }
}

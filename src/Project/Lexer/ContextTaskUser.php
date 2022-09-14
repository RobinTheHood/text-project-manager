<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTaskUser implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        if ($lexer->accept("\n")) {
            $lexer->popContext();
            $lexer->emit(Token::TYPE_NEW_LINE);
            return;
        }

        $lexer->acceptNotRun("\n");
        $lexer->emit(Token::TYPE_USER_NAME);
        return;
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_USER';
    }
}

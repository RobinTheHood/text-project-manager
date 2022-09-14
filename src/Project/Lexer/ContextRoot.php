<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextRoot implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        if ($lexer->seek('#')) {
            $lexer->resetContext(new ContextRoot());
            $lexer->pushContext(new ContextTask());
            return;
        }

        $lexer->acceptNotRun('#');
        $lexer->emit(Token::TYPE_UNKOWN_TOKEN);
    }

    public function __toString()
    {
        return 'CONTEXT_ROOT';
    }
}

<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTaskReportAmount implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        // if ($lexer->accept(".")) {
        //     $lexer->emit(Token::TYPE_SEPARATOR);
        //     return;
        // }

        // if ($lexer->accept(",")) {
        //     $lexer->emit(Token::TYPE_SEPARATOR);
        //     return;
        // }

        if ($lexer->seek('0123456789')) {
            $lexer->pushContext(new ContextNumber());
            return;
        }

        $lexer->popContext();
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_REPORT_AMOUNT';
    }
}

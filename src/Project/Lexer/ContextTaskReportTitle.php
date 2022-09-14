<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class ContextTaskReportTitle implements ContextInterface
{
    public function lex(Lexer $lexer)
    {
        $lexer->acceptNotRun(";\n");
        $lexer->emit(Token::TYPE_STRING);
        $lexer->popContext();
        return;
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_REPORT_TITLE';
    }
}

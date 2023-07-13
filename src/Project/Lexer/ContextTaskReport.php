<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

use Exception;

class ContextTaskReport implements ContextInterface
{
    private $fieldIndex = -1;

    public function lex(Lexer $lexer)
    {
        if ($this->fieldIndex === -1 && $lexer->accept('+-')) {
            $lexer->emit(Token::TYPE_REPORT_START);
            $this->fieldIndex++;
            return;
        }

        if ($lexer->accept(';')) {
            $this->fieldIndex++;
            $lexer->emit(Token::TYPE_SEPARATOR);
            return;
        }

        if ($lexer->accept("\n")) {
            $lexer->popContext();
            $lexer->emit(Token::TYPE_NEW_LINE);
            return;
        }

        if ($this->fieldIndex !== 2) { // Space nicht vom Title abziehen
            if ($lexer->accept(' ')) {
                $lexer->acceptRun(' ');
                $lexer->emit(Token::TYPE_SPACE);
                return;
            }
        }

        if ($this->fieldIndex === 0) {
            $lexer->pushContext(new ContextDate());
            return;
        }

        // if ($this->fieldIndex === 1) {
        //     $lexer->pushContext(new ContextNumber());
        //     return;
        // }

        if ($this->fieldIndex === 1) {
            $lexer->pushContext(new ContextTaskReportAmount());
            return;
        }

        if ($this->fieldIndex === 2) {
            $lexer->pushContext(new ContextTaskReportTitle());
            return;
        }

        if ($this->fieldIndex === 3) {
            if ($lexer->seek('0123456789')) {
                $lexer->pushContext(new ContextNumber());
                return;
            }
        }

        if ($this->fieldIndex === 4) {
            if ($lexer->seek('0123456789')) {
                $lexer->pushContext(new ContextNumber());
                return;
            }
        }

        throw new Exception('ContextReport Error 1');
    }

    public function __toString()
    {
        return 'CONTEXT_TASK_REPORT';
    }
}

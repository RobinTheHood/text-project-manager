<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

interface ContextInterface
{
    public function lex(Lexer $lexer);
}

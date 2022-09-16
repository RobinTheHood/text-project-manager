<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class NewLinesParser
{
    /**
     * <new_lines> ::= (<token_new_line>)*
     */
    public function parse(Parser $parser): void
    {
        while ($parser->accept(Token::TYPE_NEW_LINE));
    }
}

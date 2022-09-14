<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\ParsersNew;

use RobinTheHood\TextProjectManager\Project\Lexer\Lexer;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

class Parser
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var Token
     */
    private $currentToken;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->currentToken = $this->nextToken();
    }

    public function accept(int $tokenType): ?Token
    {
        var_dump($this->currentToken);

        // if ($this->currentToken->type === Token::TYPE_EOF) {
        //     die('EOF');
        // }

        if ($this->currentToken->type === $tokenType) {
            $token = $this->currentToken;
            $this->currentToken = $this->nextToken();
            return $token;
        }
        return null;
    }

    private function nextToken(): Token
    {
        $token = $this->lexer->getNextToken();
        while ($token->type === Token::TYPE_SPACE) {
            $token = $this->lexer->getNextToken();
        }
        return $token;
    }
}

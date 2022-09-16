<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use Exception;
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
    private $lookaheadToken;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->lookaheadToken = $this->nextToken();
    }

    public function accept(int $tokenType, $tokenString = ''): ?Token
    {
        echo 'Expect: ' . $this->lookaheadToken->typeToString($tokenType) . ' : ' . $tokenString . "\n";
        var_dump($this->lookaheadToken);
        echo "\n";

        if ($this->lookaheadToken->type !== $tokenType) {
            return null;
        }

        if ($tokenString && $this->lookaheadToken->string !== $tokenString) {
            return null;
        }

        $token = $this->lookaheadToken;
        $this->lookaheadToken = $this->nextToken();
        return $token;
    }

    public function acceptNewlineOrEndOfFile(): ?Token
    {
        if ($token = $this->accept(Token::TYPE_EOF)) {
            return $token;
        }

        if ($token = $this->accept(Token::TYPE_NEW_LINE)) {
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

    public function isEndOfFile(): bool
    {
        if ($this->lookaheadToken->type === Token::TYPE_EOF) {
            return true;
        }
        return false;
    }

    public function throwException(string $message): void
    {
        throw new Exception($message . " on line {$this->lookaheadToken->line} at positon {$this->lookaheadToken->linePosition}");
    }
}

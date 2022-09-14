<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

use RobinTheHood\TextProjectManager\Helpers\InputReaderInterface;

class Lexer extends AbstractLexer
{
    /**
     * @var InputReaderInterface
     */
    private $inputReader;

    /**
     * @var string
     */
    private $consumedString = '';

    /**
     * @var Token[]
     */
    private $emittedTokens = [];

    public function __construct(InputReaderInterface $inputReader)
    {
        $this->inputReader = $inputReader;
        $this->resetContext(new ContextRoot());
    }

    public function seek(string $chars): bool
    {
        $char = $this->inputReader->seek();
        if (strpos($chars, $char) !== false) {
            return true;
        }
        return false;
    }

    public function consume()
    {
        $this->consumedString .= $this->inputReader->consume();
    }

    public function acceptString(string $string)
    {
        $stringLen = strlen($string);
        $seekedString = $this->inputReader->seek(strlen($string));
        if ($seekedString === $string) {
            $this->consumedString .= $this->inputReader->consume($stringLen);
            return true;
        }
        return false;
    }

    public function accept(string $chars): bool
    {
        if ($this->seek($chars)) {
            $this->consumedString .= $this->inputReader->consume();
            return true;
        }
        return false;
    }

    public function acceptNotRun(string $chars)
    {
        while (!$this->inputReader->isEof()) {
            $char = $this->inputReader->seek();
            if (strpos($chars, $char) !== false) {
                break;
            }
            $this->consumedString .= $this->inputReader->consume();
        }
    }

    public function acceptRun(string $chars)
    {
        while (!$this->inputReader->isEof()) {
            $char = $this->inputReader->seek();
            if (strpos($chars, $char) === false) {
                break;
            }
            $this->consumedString .= $this->inputReader->consume();
        }
    }

    public function emit(int $type): void
    {
        $token = new Token($type, $this->consumedString);
        $this->consumedString = '';
        $this->emittedTokens[] = $token;
    }

    public function getNextToken(): Token
    {
        $token = $this->consumeToken();
        if ($token) {
            return $token;
        }

        while (!$token) {
            if ($this->inputReader->isEof()) {
                return new Token(Token::TYPE_EOF, '');
            }

            $context = $this->getContext();
            $context->lex($this);
            $token = $this->consumeToken();
        }

        return $token;
    }

    public function consumeToken(): ?Token
    {
        return array_shift($this->emittedTokens);
    }
}

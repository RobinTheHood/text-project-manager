<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

abstract class AbstractLexer
{
    /**
     * @var ContextInterface[]
     */
    private $contextStack;

    public function resetContext(ContextInterface $context)
    {
        $this->contextStack = [];
        $this->pushContext($context);
    }

    public function pushContext(ContextInterface $context): void
    {
        array_push($this->contextStack, $context);
    }

    public function popContext(): ContextInterface
    {
        return array_pop($this->contextStack);
    }

    public function getContext(): ContextInterface
    {
        return $this->contextStack[array_key_last($this->contextStack)];
    }
}

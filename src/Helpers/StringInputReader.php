<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Helpers;

class StringInputReader extends InputReader implements InputReaderInterface
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct(string $content)
    {
        $this->setContent($content);
    }
}

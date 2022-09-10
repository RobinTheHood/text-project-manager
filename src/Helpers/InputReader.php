<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Helpers;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapperInterface;
use Exception;

class InputReader
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $content = "";

    /**
     * @var int
     */
    private $pointer = 0;

    /**
     * @var int
     */
    private $contentLength = 0;

    public function __construct(FileGetContentsWrapperInterface $fileGetsContentWrapper, string $filePath)
    {
        $this->filePath = $filePath;
        $this->content = $fileGetsContentWrapper->fileGetContents($filePath);
        $this->contentLength = strlen($this->content);
    }

    public function seek(int $count = 1): string
    {
        if ($this->isEof($this->pointer + $count)) {
            throw new Exception("End of File");
        }
        return substr($this->content, $this->pointer, $count);
    }

    public function consume(int $count = 1): string
    {
        if ($this->isEof($this->pointer + $count)) {
            throw new Exception("End of File");
        }

        $string = substr($this->content, $this->pointer, $count);
        $this->pointer += $count;
        return $string;
    }

    private function isEof(int $offset): bool
    {
        if ($offset > $this->contentLength) {
            return true;
        }
        return false;
    }
}

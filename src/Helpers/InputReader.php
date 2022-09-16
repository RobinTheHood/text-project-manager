<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Helpers;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapperInterface;

class InputReader implements InputReaderInterface
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

    /**
     * @var int
     */
    private $line = 1;

    /**
     * @var int
     */
    private $linePosition = 1;

    public function __construct(FileGetContentsWrapperInterface $fileGetsContentWrapper, string $filePath)
    {
        $this->filePath = $filePath;
        $this->content = $fileGetsContentWrapper->fileGetContents($filePath);
        $this->contentLength = strlen($this->content);
    }

    public function seek(int $count = 1): string
    {
        return $this->getSubContent($this->pointer, $count);
    }

    public function consume(int $count = 1): string
    {
        $string = $this->getSubContent($this->pointer, $count);
        if (strpos($string, "\n") !== false) {
            $this->line++;
            $parts = explode("\n", $string);
            $this->linePosition = strlen($parts[1] ?? '');
        } else {
            $this->linePosition += $count;
        }
        $this->pointer += $count;
        return $string;
    }

    public function isEof(): bool
    {
        if ($this->pointer >= $this->contentLength) {
            return true;
        }
        return false;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getLinePosition(): int
    {
        return $this->linePosition;
    }

    private function getSubContent(int $offset, int $length): string
    {
        if ($offset > $this->contentLength) {
            return "";
        }

        $maxLength = $this->contentLength - $offset;
        $useLength = min($length, $maxLength);

        return substr($this->content, $offset, $useLength);
    }
}

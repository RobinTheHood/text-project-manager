<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Helpers;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapperInterface;

class FileInputReader extends InputReader implements InputReaderInterface
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct(FileGetContentsWrapperInterface $fileGetsContentWrapper, string $filePath)
    {
        $this->filePath = $filePath;
        $this->setContent($fileGetsContentWrapper->fileGetContents($filePath));
    }
}

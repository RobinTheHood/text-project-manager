<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Adapters;

class FileGetContentsWrapper implements FileGetContentsWrapperInterface
{
    public function fileGetContents(string $filePath): string
    {
        return file_get_contents($filePath);
    }
}

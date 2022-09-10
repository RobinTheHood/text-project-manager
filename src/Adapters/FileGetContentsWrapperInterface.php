<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Adapters;

interface FileGetContentsWrapperInterface
{
    public function fileGetContents(string $filePath): string;
}

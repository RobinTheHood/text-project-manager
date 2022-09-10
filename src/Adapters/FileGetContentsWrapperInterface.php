<?php

declare(strict_types=1);

namespace App\Adapters;

interface FileGetContentsWrapperInterface
{
    public function fileGetContents(string $filePath): string;
}

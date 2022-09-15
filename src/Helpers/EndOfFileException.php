<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Helpers;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapperInterface;
use RuntimeException;

class EndOfFileException extends RuntimeException
{
}

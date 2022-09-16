<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Helpers;

interface InputReaderInterface
{
    public function seek(int $count = 1): string;

    public function consume(int $count = 1): string;

    public function isEof(): bool;

    public function getLine(): int;

    public function getLinePosition(): int;
}

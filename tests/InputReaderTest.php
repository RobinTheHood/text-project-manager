<?php

declare(strict_types=1);

namespace Test;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapper;
use RobinTheHood\TextProjectManager\Helpers\InputReader;
use PHPUnit\Framework\TestCase;
use RobinTheHood\TextProjectManager\Helpers\EndOfFileException;

final class InputReaderTest extends TestCase
{
    public function setUp(): void
    {
    }

    public function testCanSeek(): void
    {
        $content = '123456789';
        $inputReader = $this->createInputReader($content);

        $string = $inputReader->seek(3);
        $expectedString = '123';
        $this->assertEquals($expectedString, $string);

        $string = $inputReader->seek(3);
        $expectedString = '123';
        $this->assertEquals($expectedString, $string);

        $string = $inputReader->seek(9);
        $expectedString = '123456789';
        $this->assertEquals($expectedString, $string);

        $string = $inputReader->seek(10);
        $expectedString = '123456789';
        $this->assertEquals($expectedString, $string);
    }

    public function testCanConsume(): void
    {
        $content = '123456789';
        $inputReader = $this->createInputReader($content);

        $string = $inputReader->consume(3);
        $expectedString = '123';
        $this->assertEquals($expectedString, $string);

        $string = $inputReader->consume(3);
        $expectedString = '456';
        $this->assertEquals($expectedString, $string);

        $string = $inputReader->consume(3);
        $expectedString = '789';
        $this->assertEquals($expectedString, $string);

        $string = $inputReader->consume();
        $expectedString = '';
        $this->assertEquals($expectedString, $string);
    }

    private function createInputReader(string $content): InputReader
    {
        $fileGetsContentWrapperMock = $this->createMock(FileGetContentsWrapper::class);
        $fileGetsContentWrapperMock->method('fileGetContents')->willReturn($content);

        $inputReader = new InputReader($fileGetsContentWrapperMock, '/no/file/path');
        return $inputReader;
    }
}

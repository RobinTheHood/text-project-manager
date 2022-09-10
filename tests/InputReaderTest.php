<?php

declare(strict_types=1);

namespace Test;

use App\Adapters\FileGetContentsWrapper;
use App\Helpers\InputReader;
use Exception;
use PHPUnit\Framework\TestCase;

final class InputReaderTest extends TestCase
{
    private $fileGetsContentWrapperMock;

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
    }

    public function testSeekThrowsExceptionOnEndOfFile1(): void
    {
        $this->expectException(Exception::class);

        $content = '';
        $inputReader = $this->createInputReader($content);
        $inputReader->seek();
    }

    public function testSeekThrowsExceptionOnEndOfFile2(): void
    {
        $this->expectException(Exception::class);

        $content = '0';
        $inputReader = $this->createInputReader($content);
        $inputReader->seek(2);
    }

    public function testConsumeThrowsExceptionOnEndOfFile1(): void
    {
        $this->expectException(Exception::class);

        $content = '123';
        $inputReader = $this->createInputReader($content);
        $inputReader->consume(5);
    }

    public function testConsumeThrowsExceptionOnEndOfFile2(): void
    {
        $this->expectException(Exception::class);

        $content = '123123';
        $inputReader = $this->createInputReader($content);
        $inputReader->consume(3);
        $inputReader->consume(4);
    }

    private function createInputReader(string $content): InputReader
    {
        $fileGetsContentWrapperMock = $this->createMock(FileGetContentsWrapper::class);
        $fileGetsContentWrapperMock->method('fileGetContents')->willReturn($content);

        $inputReader = new InputReader($fileGetsContentWrapperMock, '/no/file/path');
        return $inputReader;
    }
}

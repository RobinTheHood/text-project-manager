<?php

declare(strict_types=1);

namespace Test;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapper;
use RobinTheHood\TextProjectManager\Helpers\InputReader;
use PHPUnit\Framework\TestCase;
use RobinTheHood\TextProjectManager\Project\Lexer;
use RobinTheHood\TextProjectManager\Project\Token;

final class LexerTest extends TestCase
{
    public function testCanLexTokenNumber(): void
    {
        $lexer = $this->createLexer('123');
        $token = $lexer->getNextToken();

        $expectedToken = new Token(Token::TYPE_NUMBER, '123');

        $this->assertEquals($expectedToken, $token);
    }

    public function testCanLexTokenWord(): void
    {
        $lexer = $this->createLexer('abc');
        $token = $lexer->getNextToken();

        $expectedToken = new Token(Token::TYPE_WORD, 'abc');

        $this->assertEquals($expectedToken, $token);
    }

    public function testCanLexTokenEof(): void
    {
        $lexer = $this->createLexer('aaa');

        $token = $lexer->getNextToken();
        $expectedToken = new Token(Token::TYPE_WORD, 'aaa');
        $this->assertEquals($expectedToken, $token);

        $token = $lexer->getNextToken();
        $expectedToken = new Token(Token::TYPE_EOF, '');
        $this->assertEquals($expectedToken, $token);
    }

    public function testCanLexMultibleTokens(): void
    {
        $lexer = $this->createLexer('ab12cde345');

        $token = $lexer->getNextToken();
        $expectedToken = new Token(Token::TYPE_WORD, 'ab');
        $this->assertEquals($expectedToken, $token);

        $token = $lexer->getNextToken();
        $expectedToken = new Token(Token::TYPE_NUMBER, '12');
        $this->assertEquals($expectedToken, $token);

        $token = $lexer->getNextToken();
        $expectedToken = new Token(Token::TYPE_WORD, 'cde');
        $this->assertEquals($expectedToken, $token);
    }

    private function createLexer(string $content): Lexer
    {
        $fileGetsContentWrapperMock = $this->createMock(FileGetContentsWrapper::class);
        $fileGetsContentWrapperMock->method('fileGetContents')->willReturn($content);

        $inputReader = new InputReader($fileGetsContentWrapperMock, '/no/file/path');
        $lexer = new Lexer($inputReader);
        return $lexer;
    }
}

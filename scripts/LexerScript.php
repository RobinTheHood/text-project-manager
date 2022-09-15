<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapper;
use RobinTheHood\TextProjectManager\Helpers\InputReader;
use RobinTheHood\TextProjectManager\Project\Lexer\Lexer;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

require_once __DIR__ . '/../vendor/autoload.php';

$fileGetsContentWrapper = new FileGetContentsWrapper();
//$inputReader = new InputReader($fileGetsContentWrapper, __DIR__ . '/../data/LexerTest.md');
$inputReader = new InputReader($fileGetsContentWrapper, __DIR__ . '/../data/ProjectPlan03.md');
$lexer = new Lexer($inputReader);

do {
    $token = $lexer->getNextToken();
    echo $token . "\n";
} while ($token->type !== Token::TYPE_EOF);

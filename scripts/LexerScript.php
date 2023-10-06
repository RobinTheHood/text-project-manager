<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapper;
use RobinTheHood\TextProjectManager\Helpers\FileInputReader;
use RobinTheHood\TextProjectManager\Project\Lexer\Lexer;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;

require_once __DIR__ . '/../vendor/autoload.php';

$fileGetsContentWrapper = new FileGetContentsWrapper();
$inputReader = new FileInputReader($fileGetsContentWrapper, __DIR__ . '/../examples/ExampleContractHosting.md');

$lexer = new Lexer($inputReader);

do {
    $token = $lexer->getNextToken();
    echo $token . "\n";
} while ($token->type !== Token::TYPE_EOF);

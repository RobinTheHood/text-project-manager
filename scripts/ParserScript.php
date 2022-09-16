<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use Exception;
use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapper;
use RobinTheHood\TextProjectManager\Helpers\InputReader;
use RobinTheHood\TextProjectManager\Project\Lexer\Lexer;
use RobinTheHood\TextProjectManager\Project\Parsers\ProjectParser;
use RobinTheHood\TextProjectManager\Project\Parsers\Parser;

require_once __DIR__ . '/../vendor/autoload.php';

$fileGetsContentWrapper = new FileGetContentsWrapper();
$inputReader = new InputReader($fileGetsContentWrapper, __DIR__ . '/../data/ParserTest.md');
$lexer = new Lexer($inputReader);
$parser = new Parser($lexer);

try {
    $projectParser = new ProjectParser();
    $project = $projectParser->parse($parser);
    var_dump($project);
} catch (Exception $e) {
    echo $e;
}

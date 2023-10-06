<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use Exception;
use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapper;
use RobinTheHood\TextProjectManager\Helpers\FileInputReader;
use RobinTheHood\TextProjectManager\Project\Lexer\Lexer;
use RobinTheHood\TextProjectManager\Project\Parsers\ProjectParser;
use RobinTheHood\TextProjectManager\Project\Parsers\Parser;

require_once __DIR__ . '/../vendor/autoload.php';

$fileGetsContentWrapper = new FileGetContentsWrapper();
$inputReader = new FileInputReader($fileGetsContentWrapper, __DIR__ . '/../examples/ExampleContractHosting.md');

$lexer = new Lexer($inputReader);
$parser = new Parser($lexer);
$parser->debug = true;

try {
    $projectParser = new ProjectParser();
    $project = $projectParser->parse($parser);
    var_dump($project);
} catch (Exception $e) {
    echo $e;
}

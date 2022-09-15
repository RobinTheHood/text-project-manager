<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use RobinTheHood\TextProjectManager\Adapters\FileGetContentsWrapper;
use RobinTheHood\TextProjectManager\Helpers\InputReader;
use RobinTheHood\TextProjectManager\Project\Lexer\Lexer;
use RobinTheHood\TextProjectManager\Project\Lexer\Token;
use RobinTheHood\TextProjectManager\Project\ParsersNew\ProjectParser;
use RobinTheHood\TextProjectManager\Project\ParsersNew\Parser;
use RobinTheHood\TextProjectManager\Project\ParsersNew\TaskParser;
use RobinTheHood\TextProjectManager\Project\ParsersNew\TimeParser;

require_once __DIR__ . '/../vendor/autoload.php';

$fileGetsContentWrapper = new FileGetContentsWrapper();
$inputReader = new InputReader($fileGetsContentWrapper, __DIR__ . '/../data/ParserTest.md');
$lexer = new Lexer($inputReader);
$parser = new Parser($lexer);

// $taskParser = new TaskParser();
// $task = $taskParser->parse($parser);
// var_dump($task);

$projectParser = new ProjectParser();
$project = $projectParser->parse($parser);
var_dump($project);

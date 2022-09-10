<?php

namespace RobinTheHood\TextProjectManager\Tests;

use RobinTheHood\TextProjectManager\Project\Parsers\ProjectParser;
use RobinTheHood\TextProjectManager\Project\Creators\EvaloationCreator;

require_once __DIR__ . '/../vendor/autoload.php';

$projectParser = new ProjectParser();
$tasks = $projectParser->parseTasks();

$projectEvaluator = new EvaloationCreator();
$evaluationString = $projectEvaluator->createEvaluation($tasks);
echo $evaluationString;

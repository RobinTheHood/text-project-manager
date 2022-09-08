<?php

namespace App\Tests;

use App\Project\Parsers\ProjectParser;
use App\Project\Creators\EvaloationCreator;

require_once __DIR__ . '/../vendor/autoload.php';

$projectParser = new ProjectParser();
$tasks = $projectParser->parseTasks();

$projectEvaluator = new EvaloationCreator();
$evaluationString = $projectEvaluator->createEvaluation($tasks);
echo $evaluationString;

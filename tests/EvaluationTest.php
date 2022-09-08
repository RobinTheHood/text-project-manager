<?php

namespace App\Tests;

use App\Project\ProjectParser;
use App\Project\ProjectEvaluator;

require_once __DIR__ . '/../ProjectParser.php';
require_once __DIR__ . '/../ProjectEvaluator.php';

$projectParser = new ProjectParser();
$tasks = $projectParser->parseTasks();

$projectEvaluator = new ProjectEvaluator();
$evaluationString = $projectEvaluator->createEvaluation($tasks);
echo $evaluationString;

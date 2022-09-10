<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use RobinTheHood\TextProjectManager\Project\ProjectParser;
use RobinTheHood\TextProjectManager\Project\ProjectEvaluator;

require_once __DIR__ . '/../vendor/autoload.php';

$projectParser = new ProjectParser();
$tasks = $projectParser->parseTasks();
//var_dump($tasks);

$projectEvaluator = new ProjectEvaluator();
$string = $projectEvaluator->createBillItems($tasks);
//$string = $projectEvaluator->createEvaluation($tasks);
echo $string;

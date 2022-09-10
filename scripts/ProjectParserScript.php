<?php

namespace RobinTheHood\TextProjectManager\Tests;

use RobinTheHood\TextProjectManager\Project\ProjectParser2;

require_once __DIR__ . '/../vendor/autoload.php';

$projectParser = new ProjectParser2();
$tasks = $projectParser->parseTasks();
var_dump($tasks);

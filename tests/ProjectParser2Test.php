<?php

namespace App\Tests;

use App\Project\ProjectParser2;

require_once __DIR__ . '/../ProjectParser2.php';
require_once __DIR__ . '/../Task.php';
require_once __DIR__ . '/../Target.php';
require_once __DIR__ . '/../Report.php';
require_once __DIR__ . '/../Amount.php';

$projectParser = new ProjectParser2();
$tasks = $projectParser->parseTasks();
var_dump($tasks);

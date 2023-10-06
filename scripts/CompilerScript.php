<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use RobinTheHood\TextProjectManager\Project\Creators\Compiler;

require_once __DIR__ . '/../vendor/autoload.php';


$compiler = new Compiler();
echo $compiler->compile('> Hosting');

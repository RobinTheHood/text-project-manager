<?php

namespace RobinTheHood\TextProjectManager\Scripts;

use RobinTheHood\TextProjectManager\Helpers\StringFilter;

require_once __DIR__ . '/../vendor/autoload.php';


$filter = new StringFilter();
echo $filter->extractTextBetweenMarkers('```> HostingA```aaa```', ```);

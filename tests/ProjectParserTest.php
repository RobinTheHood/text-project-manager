<?php

declare(strict_types=1);

namespace Test;

use App\Project\Parsers\ProjectParser;
use PHPUnit\Framework\TestCase;

final class ProjectParserTest extends TestCase
{
    private $projectParser;

    public function setUp(): void
    {
        $this->projectParser = new ProjectParser();
    }

    public function test1(): void
    {
        $fileContent = file_get_contents(__DIR__ . '/../data/ProjectPlan01.txt');
        $project = $this->projectParser->parse($fileContent);
        var_dump($project);
        die();
    }
}
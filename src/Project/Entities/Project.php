<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Entities;

class Project
{
    public $name;

    /**
     * @var Task[]
     */
    public $tasks;
}

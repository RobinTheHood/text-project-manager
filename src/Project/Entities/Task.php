<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Entities;

class Task
{
    public $name;

    /**
     * @var Target
     */
    public $target;

    /**
     * @var Report[]
     */
    public $reports;

    public $parentTask;
}

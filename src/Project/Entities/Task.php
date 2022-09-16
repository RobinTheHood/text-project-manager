<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Entities;

class Task
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var Description
     */
    public $description;

    /**
     * @var Target
     */
    public $target;

    /**
     * @var User[]
     */
    public $users;

    /**
     * @var Task[]
     */
    public $childTasks;
}

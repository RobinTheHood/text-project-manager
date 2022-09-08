<?php

declare(strict_types=1);

namespace App\Project\Entities;

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

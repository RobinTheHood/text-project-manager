<?php

namespace App\Project;

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

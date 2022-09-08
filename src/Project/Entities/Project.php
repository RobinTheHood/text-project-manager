<?php

declare(strict_types=1);

namespace App\Project\Entities;

class Project
{
    public $name;

    /**
     * @var Task[]
     */
    public $tasks;
}

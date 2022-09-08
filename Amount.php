<?php

namespace App\Project;

class Amount
{
    public const TYPE_TIME = 0;
    public const TYPE_FIX = 1;

    /**
     * @var float;
     */
    public $value;

    /**
     * @var int;
     */
    public $type;
}

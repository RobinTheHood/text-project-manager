<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Entities;

class Time
{
    public const TYPE_MINUTE = 0;
    public const TYPE_HOUR = 1;
    public const TYPE_MIXED = 2;

    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $type;
}

<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Entities;

class Description
{
    public const TYPE_HIDDEN = 0;
    public const TYPE_VISABLE = 1;

    /**
     * @var int
     */
    public $type;

    /**
     * @var string;
     */
    public $value;
}

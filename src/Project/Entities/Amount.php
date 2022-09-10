<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Entities;

class Amount
{
    public const TYPE_TIME = 0;
    public const TYPE_QUANTITY = 1;

    /**
     * @var float;
     */
    public $value;

    /**
     * @var int;
     */
    public $type;
}

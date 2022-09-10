<?php

declare(strict_types=1);

namespace App\Project\Entities;

class Target
{
    /**
     * Kein SOLL festgelegt
     */
    public const TYPE_NONE = 0;

    /**
     * SOLL Zeit festgelegt
     */
    public const TYPE_TIME = 1;

    /**
     * SOLL Zeit-Spanne festgelegt
     */
    public const TYPE_TIME_RANGE = 2;

    /**
     * SOLL Festpreis festgelegt
     */
    public const TYPE_MONEY = 3;

    /**
     * SOLL Festpreis-Spanne festgelegt
     */
    public const TYPE_MONEY_RANGE = 4;

    public $value;
    public $type;
}

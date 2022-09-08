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
    public const TYPE_TIME_SPAN = 2;

    /**
     * SOLL Festpreis festgelegt
     */
    public const TYPE_FIX = 3;

    /**
     * SOLL Festpreis-Spanne festgelegt
     */
    public const TYPE_FIX_SPAN = 4;

    public $value;
    public $type;
}

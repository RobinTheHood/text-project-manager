<?php

namespace App\Project;

class Report
{
    public const TYPE_BILLABLE = 0;
    public const TYPE_UNBILLABLE = 1;

    public $date;

    /**
     * @var Amount
     */
    public $amount;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $type;
}

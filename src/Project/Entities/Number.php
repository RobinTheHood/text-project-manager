<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Entities;

class Number
{
    /**
     * @var float
     */
    public $value;

    /**
     * @var string
     */
    public $unit = '';


    public function convertToDuration(): ?Duration
    {
        if ($this->unit === '') {
            $minutes = (int) $this->value;
        } elseif ($this->unit === 'min') {
            $minutes = (int) $this->value;
        } elseif ($this->unit === 'h') {
            $minutes = (int) ($this->value * 60);
        } else {
            return null;
        }

        $duration = new Duration();
        $duration->minutes = $minutes;
        return $duration;
    }

    public function convertToMoney(): ?Money
    {
        if ($this->unit !== '€') {
            return null;
        }
        $money = new Money();
        $money->value = $this->value;
        return $money;
    }

    public function convertToQuantity(): ?Quantity
    {
        if ($this->unit === 'x') {
            $value = (int) $this->value;
        } elseif ($this->unit === 'Stk') {
            $value = (int) $this->value;
        } else {
            return null;
        }

        $money = new Quantity();
        $money->value = $value;
        return $money;
    }

    public function contertNumberToAmount(): ?Amount
    {
        $amount = new Amount();

        $duration = $this->convertToDuration();
        if ($duration) {
            $amount->value = $duration;
            return $amount;
        }

        $quantity = $this->convertToQuantity();
        if ($quantity) {
            $amount->value = $quantity;
            return $amount;
        }

        return null;
    }
}

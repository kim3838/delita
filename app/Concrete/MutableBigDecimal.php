<?php

namespace App\Concrete;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class MutableBigDecimal
{

    private BigDecimal $value;

    public function __construct($initial = '0')
    {
        $this->value = BigDecimal::of($initial);
    }

    public function plus($that): MutableBigDecimal
    {
        $this->value = $this->value->plus($that);

        return $this;
    }

    public function minus($that): MutableBigDecimal
    {
        $this->value = $this->value->minus($that);

        return $this;
    }

    public function multiply($that): MutableBigDecimal
    {
        $this->value = $this->value->multipliedBy($that);

        return $this;
    }

    public function dividedBy($that, ?int $scale = null, RoundingMode $roundingMode = RoundingMode::Unnecessary): MutableBigDecimal
    {
        $this->value = $this->value->dividedBy($that, $scale, $roundingMode);

        return $this;
    }

    public function shallow(): BigDecimal
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}

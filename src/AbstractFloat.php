<?php

declare(strict_types=1);

namespace Mom\Data;

abstract class AbstractFloat extends AbstractValue
{
    public static function fromFloat(float $value): static
    {
        return new static($value);
    }

    public static function fromNullableFloat(?float $value): static
    {
        return new static($value);
    }

    public static function forArrayValue(AbstractValue $value): ?float
    {
        if ($value instanceof AbstractFloat) {
            return $value->toNullableFloat();
        }

        return null;
    }

    public function toPrimitive(): ?float
    {
        return $this->toNullableFloat();
    }

    public function toNullableFloat(): ?float
    {
        $value = $this->toValue();

        if (is_float($value)) {
            return $value;
        }

        if ($value instanceof AbstractFloat) {
            return $value->toNullableFloat();
        }

        return null;
    }

    public function toFloat(): float
    {
        return $this->toNullableFloat() ?? 0.0;
    }
}

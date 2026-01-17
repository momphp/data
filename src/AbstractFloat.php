<?php

declare(strict_types=1);

namespace Mom\Data;

use Illuminate\Http\Request;

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

    public static function forArrayValue(AbstractValue $value, AbstractData $data): ?float
    {
        if ($value instanceof AbstractFloat) {
            return $value->toNullableFloat();
        }

        return null;
    }

    public static function forEncryptedArrayValue(AbstractValue $value, AbstractData $data): mixed
    {
        if ($value instanceof AbstractFloat) {
            return $value->toNullableEncrypted();
        }

        return null;
    }

    public static function forResourceValue(AbstractValue $value, Request $request): ?float
    {
        if ($value instanceof AbstractFloat) {
            return $value->toNullableFloat();
        }

        return null;
    }

    public static function forDatabaseCreateValue(AbstractValue $value, AbstractData $data): ?float
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseUpdateValue(AbstractValue $value, AbstractData $data): ?float
    {
        return static::forArrayValue($value, $data);
    }

    public static function forEloquentFactoryValue(): ?float
    {
        return fake()->randomFloat();
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

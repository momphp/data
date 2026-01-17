<?php

declare(strict_types=1);

namespace Mom\Data;

abstract class AbstractInteger extends AbstractValue
{
    public static function fromInteger(int $value): static
    {
        return new static($value);
    }

    public static function fromNullableInteger(?int $value): static
    {
        return new static($value);
    }

    public static function forArrayValue(AbstractValue $value, AbstractData $data): ?int
    {
        if ($value instanceof AbstractInteger) {
            return $value->toNullableInteger();
        }

        return null;
    }

    public static function forEncryptedArrayValue(AbstractValue $value, AbstractData $data): ?string
    {
        if ($value instanceof AbstractInteger) {
            return $value->toNullableEncrypted();
        }

        return null;
    }

    public static function forResourceValue(AbstractValue $value, AbstractData $data): ?int
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseCreateValue(AbstractValue $value, AbstractData $data): ?int
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseUpdateValue(AbstractValue $value, AbstractData $data): ?int
    {
        return static::forArrayValue($value, $data);
    }

    public static function forEloquentFactoryValue(): ?int
    {
        return fake()->randomNumber();
    }

    public function toPrimitive(): ?int
    {
        return $this->toNullableInteger();
    }

    public function toNullableInteger(): ?int
    {
        $value = $this->toValue();

        if (is_int($value)) {
            return $value;
        }

        if ($value instanceof AbstractInteger) {
            return $value->toNullableInteger();
        }

        return null;
    }

    public function toInteger(): int
    {
        return $this->toNullableInteger() ?? 0;
    }
}

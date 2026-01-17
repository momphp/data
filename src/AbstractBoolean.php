<?php

declare(strict_types=1);

namespace Mom\Data;

abstract class AbstractBoolean extends AbstractValue
{
    abstract public function default(): bool;

    public static function fromBoolean(bool $value): static
    {
        return new static($value);
    }

    public static function fromNullableBoolean(?bool $value): static
    {
        return new static($value);
    }

    public static function forArrayValue(AbstractValue $value, AbstractData $data): ?bool
    {
        if ($value instanceof AbstractBoolean) {
            return $value->toBoolean();
        }

        return null;
    }

    public static function forEncryptedArrayValue(AbstractValue $value, AbstractData $data): ?bool
    {
        return static::forArrayValue($value, $data);
    }

    public static function forResourceValue(AbstractValue $value, AbstractData $data): ?bool
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseCreateValue(AbstractValue $value, AbstractData $data): ?bool
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseUpdateValue(AbstractValue $value, AbstractData $data): ?bool
    {
        return static::forArrayValue($value, $data);
    }

    public static function forEloquentFactoryValue(): bool
    {
        return fake()->boolean();
    }

    public function toPrimitive(): ?bool
    {
        return $this->toNullableBoolean();
    }

    public function toNullableBoolean(): ?bool
    {
        $value = $this->toValue();

        if (is_bool($value)) {
            return $value;
        }

        if ('1' === $value || 1 === $value || 'true' === $value || 'yes' === $value || 'on' === $value) {
            return true;
        }

        if ('0' === $value || 0 === $value || 'false' === $value || 'no' === $value || 'off' === $value) {
            return false;
        }

        if ($value instanceof AbstractBoolean) {
            return $value->toNullableBoolean();
        }

        return null;
    }

    public function toBoolean(): bool
    {
        return $this->toNullableBoolean() ?? $this->default();
    }
}

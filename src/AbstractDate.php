<?php

declare(strict_types=1);

namespace Mom\Data;

use Carbon\CarbonImmutable;

abstract class AbstractDate extends AbstractValue
{
    public static function fromCarbon(CarbonImmutable $value): static
    {
        return new static($value);
    }

    public static function fromNullableCarbon(?CarbonImmutable $value): static
    {
        return new static($value);
    }

    public static function forArrayValue(AbstractValue $value, AbstractData $data): ?string
    {
        if ($value instanceof AbstractDate) {
            return $value->toNullableISOString();
        }

        return null;
    }

    public static function forEncryptedArrayValue(AbstractValue $value, AbstractData $data): ?string
    {
        if ($value instanceof AbstractDate) {
            return $value->toNullableEncrypted();
        }

        return null;
    }

    public static function forResourceValue(AbstractValue $value, AbstractData $data): ?string
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseCreateValue(AbstractValue $value, AbstractData $data): mixed
    {
        if ($value instanceof AbstractDate) {
            return $value->toNullableDateTimeString();
        }

        return null;
    }

    public static function forDatabaseUpdateValue(AbstractValue $value, AbstractData $data): mixed
    {
        return static::forDatabaseCreateValue($value, $data);
    }

    public static function forEloquentFactoryValue(): ?string
    {
        return fake()->dateTime()->format('Y-m-d H:i:s');
    }

    public function toPrimitive(): ?CarbonImmutable
    {
        return $this->toNullableCarbon();
    }

    public function toNullableCarbon(): ?CarbonImmutable
    {
        $value = $this->toValue();

        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        if (is_string($value)) {
            return CarbonImmutable::parse($value);
        }

        if ($value instanceof AbstractDate) {
            return $value->toNullableCarbon();
        }

        return null;
    }

    public function toCarbon(): CarbonImmutable
    {
        return $this->toNullableCarbon() ?? CarbonImmutable::now();
    }

    public function toNullableDateTimeString(): ?string
    {
        return $this->toNullableCarbon()?->toDateTimeString();
    }

    public function toDateTimeString(): string
    {
        return $this->toCarbon()->toDateTimeString();
    }

    public function toISOString(): string
    {
        return $this->toCarbon()->toISOString();
    }

    public function toNullableISOString(): ?string
    {
        return $this->toNullableCarbon()?->toISOString();
    }

    public function toNullableDateString(): ?string
    {
        return $this->toNullableCarbon()?->toDateString();
    }

    public function toDateString(): string
    {
        return $this->toCarbon()->toDateString();
    }
}

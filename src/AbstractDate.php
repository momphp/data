<?php

declare(strict_types=1);

namespace Mom\Data;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\Request;

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

    public static function forResourceValue(AbstractValue $value, Request $request): ?string
    {
        if ($value instanceof AbstractDate) {
            return $value->toNullableISOString();
        }

        return null;
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

        // Covers mutable Carbon and plain DateTime instances.
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        // An empty string would otherwise parse as "now".
        if (is_string($value) && '' !== mb_trim($value)) {
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
        return static::formatISOString($this->toCarbon());
    }

    public function toNullableISOString(): ?string
    {
        $carbon = $this->toNullableCarbon();

        return null === $carbon ? null : static::formatISOString($carbon);
    }

    public function toNullableDateString(): ?string
    {
        return $this->toNullableCarbon()?->toDateString();
    }

    public function toDateString(): string
    {
        return $this->toCarbon()->toDateString();
    }

    /**
     * Same output as Carbon's toISOString(), which is about ten times slower because it
     * goes through isoFormat(). Years outside 1-9999 use Carbon's extended year format.
     */
    protected static function formatISOString(CarbonImmutable $carbon): string
    {
        $utc = $carbon->utc();

        if ($utc->year < 1 || $utc->year > 9999) {
            return $carbon->toISOString();
        }

        return $utc->format('Y-m-d\TH:i:s.u\Z');
    }
}

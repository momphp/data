<?php

declare(strict_types=1);

namespace Mom\Data;

use Illuminate\Http\Request;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractString extends AbstractValue
{
    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public static function fromNullableString(?string $value): static
    {
        return new static($value);
    }

    public static function forArrayValue(AbstractValue $value, AbstractData $data): ?string
    {
        if ($value instanceof AbstractString) {
            return $value->toNullableString();
        }

        return null;
    }

    public static function forEncryptedArrayValue(AbstractValue $value, AbstractData $data): ?string
    {
        if ($value instanceof AbstractString) {
            return $value->toNullableEncrypted();
        }

        return null;
    }

    public static function forResourceValue(AbstractValue $value, Request $request): ?string
    {
        if ($value instanceof AbstractString) {
            return $value->toNullableString();
        }

        return null;
    }

    public static function forDatabaseCreateValue(AbstractValue $value, AbstractData $data): ?string
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseUpdateValue(AbstractValue $value, AbstractData $data): ?string
    {
        return static::forDatabaseCreateValue($value, $data);
    }

    public static function forEloquentFactoryValue(): ?string
    {
        return fake()->word();
    }

    public function toPrimitive(): ?string
    {
        return $this->toNullableString();
    }

    public function toNullableString(): ?string
    {
        $value = $this->toValue();

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof UuidInterface) {
            return $value->toString();
        }

        if ($value instanceof AbstractString) {
            return $value->toNullableString();
        }

        return null;
    }

    public function toString(): string
    {
        return $this->toNullableString() ?? '';
    }
}

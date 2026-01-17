<?php

declare(strict_types=1);

namespace Mom\Data;

use Closure;
use Illuminate\Http\Request;

abstract class AbstractDataValue extends AbstractValue
{
    abstract public function toNullableData(): ?AbstractData;

    abstract public function toData(): AbstractData;

    public static function new(): static
    {
        return new static();
    }

    public static function fromClosure(Closure $value): static
    {
        return new static($value);
    }

    public static function forArrayValue(AbstractValue $value, AbstractData $data): ?array
    {
        if ($value instanceof AbstractDataValue) {
            return $value->toNullableArray();
        }

        return null;
    }

    public static function forEncryptedArrayValue(AbstractValue $value, AbstractData $data): mixed
    {
        if ($value instanceof AbstractDataValue) {
            return $value->toNullableEncrypted();
        }

        return null;
    }

    public static function forResourceValue(AbstractValue $value, Request $request): ?array
    {
        if ($value instanceof AbstractDataValue) {
            return $value->toNullableResource($request);
        }

        return null;
    }

    public static function forDatabaseCreateValue(AbstractValue $value, AbstractData $data): ?array
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseUpdateValue(AbstractValue $value, AbstractData $data): ?array
    {
        return static::forArrayValue($value, $data);
    }

    public static function forEloquentFactoryValue(): ?array
    {
        return [];
    }

    public function isNull(): bool
    {
        return null === $this->toNullableData();
    }

    public function toArray(): array
    {
        return $this->toData()->toArray();
    }

    public function toNullableArray(): ?array
    {
        $data = $this->toNullableData();

        if ($data instanceof AbstractData) {
            return $data->toArray();
        }

        return null;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function toNullableJson(): ?string
    {
        $value = $this->toNullableArray();

        if (null === $value) {
            return null;
        }

        return json_encode($this->toArray());
    }

    public function toResource(Request $request): array
    {
        return $this->toData()->forResource($request);
    }

    public function toNullableResource(Request $request): ?array
    {
        return $this->toNullableData()?->forResource($request);
    }

    public function toPrimitive(): ?array
    {
        return $this->toNullableData()->toArray();
    }
}

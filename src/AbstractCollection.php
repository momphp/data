<?php

declare(strict_types=1);

namespace Mom\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class AbstractCollection extends AbstractValue
{
    public static function fromArray(array $value): static
    {
        return new static($value);
    }

    public static function fromNullableArray(?array $value): static
    {
        return new static($value);
    }

    public static function fromCollection(Collection $value): static
    {
        return new static($value);
    }

    public static function fromNullableCollection(?Collection $value): static
    {
        return new static($value);
    }

    public static function forArrayValue(AbstractValue $value, AbstractData $data): ?array
    {
        if ($value instanceof AbstractCollection) {
            return $value->toNullableArray();
        }

        return null;
    }

    public static function forEncryptedArrayValue(AbstractValue $value, AbstractData $data): ?string
    {
        if ($value instanceof AbstractCollection) {
            return $value->toNullableEncrypted();
        }

        return null;
    }

    public static function forResourceValue(AbstractValue $value, Request $request): ?array
    {
        if ($value instanceof AbstractCollection) {
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

    public function toResource(Request $request): array
    {
        return $this->toNullableResource($request) ?? [];
    }

    public function toNullableResource(Request $request): ?array
    {
        return $this->toNullableCollection()
            ?->map(function (mixed $item) use ($request) {
                if ($item instanceof AbstractData) {
                    return $item->forResource($request);
                }

                return $item;
            })
            ?->toArray();
    }

    public function toPrimitive(): ?Collection
    {
        return $this->toNullableCollection();
    }

    public function toNullableCollection(): ?Collection
    {
        $value = $this->toValue();

        if ($value instanceof Collection && $value->isNotEmpty()) {
            return $value;
        }

        if (is_array($value) && count($value) > 0) {
            return collect($value);
        }

        if (is_string($value) && ! empty($value)) {
            return collect([$value]);
        }

        if ($value instanceof self) {
            return $value->toNullableCollection();
        }

        return null;
    }

    public function toCollection(): Collection
    {
        return $this->toNullableCollection() ?? collect();
    }

    public function toNullableArray(): ?array
    {
        return $this->toNullableCollection()?->toArray();
    }

    public function toArray(): array
    {
        return $this->toCollection()->toArray();
    }
}

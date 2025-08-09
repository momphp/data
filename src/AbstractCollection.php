<?php

declare(strict_types=1);

namespace Mom\Data;

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

    public static function forResourceValue(AbstractValue $value, AbstractData $data): ?array
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseCreateValue(AbstractValue $value, AbstractData $data): ?array
    {
        return static::forArrayValue($value, $data);
    }

    public static function forDatabaseUpdateValue(AbstractValue $value, AbstractData $data): ?array
    {
        return static::forArrayValue($value, $data);
    }

    public static function forEloquentFactoryValue(AbstractValue $value): ?array
    {
        return [];
    }

    public function toPrimitive(): ?Collection
    {
        return $this->toNullableCollection();
    }

    public function toNullableCollection(): ?Collection
    {
        $value = $this->toValue();

        if ($value instanceof Collection) {
            return $value;
        }

        if (is_array($value)) {
            return collect($value);
        }

        if ($value instanceof AbstractCollection) {
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

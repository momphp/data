<?php

declare(strict_types=1);

namespace Mom\Data;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractEnum extends AbstractValue
{
    abstract public function toNullableEnum(): ?BackedEnum;

    abstract public function toEnum(): BackedEnum;

    public static function fromEloquentModel(Model $model): static
    {
        $value = $model->getAttributes()[static::getDatabaseTableColumnName()] ?? null;

        return new static(value: $value);
    }

    public static function forArrayValue(AbstractValue $value): ?string
    {
        if ($value instanceof AbstractEnum) {
            return $value->toNullableString();
        }

        return null;
    }

    public static function fromArray(array $item): static
    {
        return new static(value: $item[static::getName()] ?? null);
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public static function fromInteger(int $value): static
    {
        return new static($value);
    }

    public static function fromNullableString(?string $value): static
    {
        return new static($value);
    }

    public static function fromEnum(BackedEnum $value): static
    {
        return new static($value);
    }

    public static function fromNullableEnum(?BackedEnum $value): static
    {
        return new static($value);
    }

    public function toNullableString(): ?string
    {
        $enum = $this->toNullableEnum();

        if (null === $enum) {
            return null;
        }

        return (string) $enum->value;
    }

    public function toString(): string
    {
        return (string) $this->toEnum()->value;
    }

    public function toNullableInteger(): ?int
    {
        return (int) $this->toNullableString();
    }

    public function toInteger(): int
    {
        return (int) $this->toString();
    }

    public function toPrimitive(): ?string
    {
        return $this->toNullableString();
    }

    public function isNull(): bool
    {
        return null === $this->toNullableString();
    }
}

<?php

declare(strict_types=1);

namespace Mom\Data;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

abstract class AbstractValue
{
    private mixed $data = null;

    final public function __construct(
        protected readonly mixed $value = null,
    ) {}

    abstract public function toPrimitive(): mixed;

    abstract public static function getName(): string;

    public static function fromEloquentModel(Model $model): static
    {
        $value = $model->getAttributes()[static::getDatabaseTableColumnName()] ?? null;

        return (new static(value: $value))
            ->setData($model);
    }

    public static function fromDataArray(array $item): static
    {
        $value = $item[static::getName()] ?? null;

        return (new static(value: $value))
            ->setData($item);
    }

    public static function getDatabaseTableColumnName(): string
    {
        return static::getName();
    }

    public static function getNameForHuman(): string
    {
        return static::getName() . '_for_human';
    }

    public static function fromData(AbstractData $data): static
    {
        return new static(value: $data);
    }

    public static function new(): static
    {
        return new static();
    }

    public static function from($item): static
    {
        if ($item instanceof Model) {
            return static::new()->fromEloquentModel($item);
        }

        return new static(value: $item);
    }

    public static function getDatabaseTableColumnNameWithTable(BackedEnum|string|null $table = null): string
    {
        if (null === $table) {
            return static::getDatabaseTableColumnName();
        }

        if ($table instanceof BackedEnum) {
            $table = $table->value;
        }

        return "{$table}." . static::getDatabaseTableColumnName();
    }

    public static function getAliasedColumnNameWithTableForSelect(BackedEnum|string|null $table = null): string
    {
        if (null === $table) {
            return static::getAliasedColumnName($table);
        }

        if ($table instanceof BackedEnum) {
            $table = $table->value;
        }

        return "{$table}." . static::getDatabaseTableColumnName() . ' as ' . static::getAliasedColumnName($table);
    }

    public static function getAliasedColumnNameWithTable(BackedEnum|string|null $table = null): string
    {
        if (null === $table) {
            return static::getAliasedColumnName($table);
        }

        if ($table instanceof BackedEnum) {
            $table = $table->value;
        }

        return "{$table}." . static::getAliasedColumnName($table);
    }

    public static function getAliasedColumnName(BackedEnum|string|null $table = null): string
    {
        if (null === $table) {
            return static::getName();
        }

        if ($table instanceof BackedEnum) {
            $table = $table->value;
        }

        return "{$table}_" . static::getName();
    }

    public static function getNameForValidation(?string $parent = null, bool $isArray = false, bool $associative = false): string
    {
        if (null === $parent && false === $isArray) {
            return static::getName();
        }

        if ($isArray && $associative) {
            return $parent . '.*.' . static::getName();
        }

        if ($isArray && null !== $parent) {
            return $parent . '.*';
        }

        if ($isArray && null === $parent) {
            return static::getName() . '.*';
        }

        return $parent . '.' . static::getName();
    }

    public static function getNameForErrorMessage(string $rule, ?string $parent = null): string
    {
        if (null === $parent) {
            return static::getName() . '.' . $rule;
        }

        return $parent . '.*.' . static::getName() . '.' . $rule;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function isNull(): bool
    {
        $value = $this->toValue();

        return null === $value;
    }

    public function isNotNull(): bool
    {
        return false === $this->isNull();
    }

    public function toEncrypted(): string
    {
        $value = $this->toPrimitive();

        try {
            return encrypt(decrypt($value));
        } catch (DecryptException) {
            return encrypt($value);
        }
    }

    public function toNullableEncrypted(): ?string
    {
        if ($this->isNull()) {
            return null;
        }

        return $this->toEncrypted();
    }

    public function toDecrypted(): string
    {
        try {
            return decrypt($this->toPrimitive());
        } catch (DecryptException) {
            return $this->toPrimitive() ?? '';
        }
    }

    public function toNullableDecrypted(): ?string
    {
        if ($this->isNull()) {
            return null;
        }

        return $this->toDecrypted();
    }

    public function toHashed(): string
    {
        $value = $this->toDecrypted();

        if (Hash::isHashed($value)) {
            return $value;
        }

        return Hash::make($value);
    }

    public function toValue(): mixed
    {
        if ($this->value instanceof Closure) {
            return ($this->value)();
        }

        return $this->value;
    }

    public function equalsTo(mixed $value): bool
    {
        return $this->toValue() === $value;
    }
}

<?php

declare(strict_types=1);

namespace Mom\Data;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use LogicException;

abstract class Value
{
    private ?Model $eloquentModel = null;

    private mixed $item = null;

    final public function __construct(
        protected readonly mixed $value = null,
    ) {}

    abstract public static function fromEloquentModel(Model $model);

    abstract public static function fromArray(array $item);

    abstract public function isNull(): bool;

    abstract public function toPrimitive(): mixed;

    public static function getName(): string
    {
        throw new LogicException('Method not implemented');
    }

    public static function getDatabaseTableColumnName(): string
    {
        return static::getName();
    }

    public static function getNameForHuman(): string
    {
        return static::getName() . '_for_human';
    }

    public static function fromData(Data $data): static
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

        if (is_array($item)) {
            return static::new()->fromArray($item);
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

    public function getEloquentModel(): ?Model
    {
        return $this->eloquentModel;
    }

    public function setEloquentModel(?Model $eloquentModel): static
    {
        $this->eloquentModel = $eloquentModel;

        return $this;
    }

    public function getItem(): mixed
    {
        return $this->item;
    }

    public function setItem(mixed $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function isNotNull(): bool
    {
        return false === $this->isNull();
    }

    public function toEncrypted(): string
    {
        try {
            $temp = decrypt($this->toPrimitive());

            return encrypt($temp);
        } catch (DecryptException) {
            return $this->toPrimitive() ?? '';
        }
    }

    public function toDecrypted(): string
    {
        try {
            return decrypt($this->toPrimitive());
        } catch (DecryptException) {
            return $this->toPrimitive() ?? '';
        }
    }

    public function toHashed(): string
    {
        return Hash::make($this->toDecrypted());
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

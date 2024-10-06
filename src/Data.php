<?php

namespace Mom\Data;

use BackedEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Metatech\Support\Abstracts\AbstractData;
use Metatech\Support\Abstracts\AbstractString;
use Metatech\Support\Abstracts\AbstractValue;
use Metatech\Support\Enums\MorphMapKeyEnum;
use ReflectionClass;
use RuntimeException;
use stdClass;

abstract class Data
{
    private ?Model $eloquentModel = null;

    private ?BackedEnum $morphAlias = null;

    private bool $existsInDatabase = false;

    public static function fake(array $attributes = [], array $with = [], bool $persist = true): static
    {
        $factory = static::getFactory();

        if ($persist === true) {
            $model = $factory->create($attributes);

            return self::fromEloquentModel($model->load($with))->setExistsInDatabase(true);
        }

        $model = $factory->make($attributes);

        return self::fromEloquentModel($model);
    }

    public static function fakeCollection(array $attributes = [], int $count = 2, array $with = [], bool $persist = true): Collection
    {
        $factory = static::getFactory();

        if ($persist === true) {
            $models = $factory->count($count)->create($attributes);

            return self::collect($models);
        }

        $models = $factory->count($count)->make($attributes);

        return self::collect($models);
    }

    public static function new(): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return [$property->name => $name::new()];
            })->toArray();

        return new static(...$properties);
    }

    public static function fromArray(array $item): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property) use ($item): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return [$property->name => $name::fromArray($item)];
            })->toArray();

        return new static(...$properties);
    }

    public static function fromEloquentModel(?Model $model): static
    {
        if ($model === null) {
            return static::new();
        }

        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property) use ($model): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return [$property->name => $name::fromEloquentModel($model)];
            })->toArray();

        return (new static(...$properties))
            ->setEloquentModel($model)
            ->setMorphAlias(MorphMapKeyEnum::fromEloquentModel($model));
    }

    public static function fromData(AbstractData $data, string $method = 'fromData', mixed $options = null): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property) use ($data, $method, $options): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                if (method_exists($name, $method) === false) {
                    return [$property->name => $name::new()];
                }

                return [$property->name => $name::$method($data, $options)];
            })->toArray();

        return new static(...$properties);
    }

    public static function fromStandardClass(stdClass $data, mixed $options = null): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (\ReflectionProperty $property) use ($data, $options): array {
                $method = 'fromStandardClass';

                /** @var \ReflectionNamedType $type */
                $type = $property->getType();

                /** @var DataValue $name */
                $name = $type->getName();

                if (method_exists($name, $method) === false) {
                    return [$property->name => $name::new()];
                }

                return [$property->name => $name::$method($data, $options)];
            })->toArray();

        return new static(...$properties);
    }

    public static function collect(Collection|array $items): Collection
    {
        if (is_array($items)) {
            $items = collect($items);
        }

        return $items->map(function (mixed $item) {
            if ($item instanceof Model) {
                return static::fromEloquentModel($item);
            }

            if (is_array($item)) {
                return static::fromArray($item);
            }

            if ($item instanceof Data) {
                return static::fromData($item);
            }

            if ($item instanceof stdClass) {
                return static::fromStandardClass($item);
            }

            return $item;
        });
    }

    public static function from($item): static
    {
        if ($item instanceof Model) {
            return static::fromEloquentModel($item);
        }

        if (is_array($item)) {
            return static::fromArray($item);
        }

        if ($item instanceof Data) {
            return static::fromData($item);
        }

        return static::new();
    }

    public static function getFactory(): Factory
    {
        throw new RuntimeException('The getFactory method must be implemented.');
    }

    public function getEloquentModel(): ?Model
    {
        return $this->eloquentModel;
    }

    public function setEloquentModel(Model $eloquentModel): static
    {
        $this->eloquentModel = $eloquentModel;

        return $this;
    }

    public function isNull(): bool
    {
        return $this->getPrimaryKey()->isNull();
    }

    public function isNotNull(): bool
    {
        return $this->isNull() === false;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray(): array
    {
        $class = new ReflectionClass($this);

        return collect($class->getProperties())
            ->filter(function (ReflectionProperty $property) {
                $newKey = Str::snake($property->name);

                if (array_key_exists($newKey, $this->getExceptKeys()) && $this->getExceptKeys()[$newKey] === null) {
                    return false;
                }

                if (array_key_exists($newKey, $this->getOnlyKeys())) {
                    return true;
                }

                return ! (count($this->getOnlyKeys()) > 0);
            })
            ->filter(fn (ReflectionProperty $property) => method_exists($this, 'get'.Str::studly($property->name)))
            ->mapWithKeys(function (ReflectionProperty $property) {
                $method = 'get'.Str::studly($property->name);

                /** @var AbstractValue $instance */
                $instance = $this->{$method}();

                if ($this->forEvent !== null) {
                    $instance->forEvent($this->forEvent);
                }

                $newKey = Str::snake($property->name);

                if (array_key_exists($newKey, $this->getExceptKeys()) && $this->getExceptKeys()[$newKey] !== null) {
                    $instance->except([$this->getExceptKeys()[$newKey]]);
                }

                if (array_key_exists($newKey, $this->getOnlyKeys()) && $this->getOnlyKeys()[$newKey] !== null) {
                    $instance->only([$this->getOnlyKeys()[$newKey]]);
                }

                return [$newKey => $instance->toPrimitive()];
            })
            ->sortKeys()
            ->toArray();
    }

    public function getOnlyKeys(): array
    {
        return collect($this->onlyKeys)
            ->mapWithKeys(function (string $value) {
                $pieces = explode('.', $value);

                if (count($pieces) === 1) {
                    return [$value => null];
                }

                $first = array_shift($pieces);

                return [$first => implode('.', $pieces)];
            })
            ->toArray();
    }

    public function getExceptKeys(): array
    {
        return collect($this->exceptKeys)
            ->mapWithKeys(function (string $value) {
                $pieces = explode('.', $value);

                if (count($pieces) === 1) {
                    return [$value => null];
                }

                $first = array_shift($pieces);

                return [$first => implode('.', $pieces)];
            })
            ->toArray();
    }

    public function only(array $keys): self
    {
        $this->onlyKeys = array_merge($this->onlyKeys, $keys);

        return $this;
    }

    public function except(array $keys): self
    {
        $this->exceptKeys = array_merge($this->exceptKeys, $keys);

        return $this;
    }

    public function setExistsInDatabase(bool $existsInDatabase): AbstractData
    {
        $this->existsInDatabase = $existsInDatabase;

        return $this;
    }

    public function existsInDatabase(): bool
    {
        return $this->existsInDatabase;
    }

    public function notExistsInDatabase(): bool
    {
        return $this->existsInDatabase() === false;
    }

    public function forDatabase(): array
    {
        return [];
    }

    public function forDatabaseCreate(): array
    {
        return [];
    }

    public function forDatabaseUpdate(): array
    {
        return [];
    }

    public function isDirty(): bool
    {
        return false;
    }

    public function toArrayForUser(): array
    {
        return [];
    }

    public function getMorphAlias(): MorphMapKeyEnum
    {
        return $this->morphAlias;
    }

    public function setMorphAlias(MorphMapKeyEnum $morphAlias): static
    {
        $this->morphAlias = $morphAlias;

        return $this;
    }

    public function toEncryptedArray(): array
    {
        return [];
    }

    public function forResource(): array
    {
        return [];
    }

    public function toResource(): JsonResource
    {
        throw new RuntimeException('The toResource method must be implemented.');
    }

    public function getTitle(): AbstractString
    {
        throw new RuntimeException('The getTitle method must be implemented.');
    }
}

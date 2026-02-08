<?php

declare(strict_types=1);

namespace Mom\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use RuntimeException;
use stdClass;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @phpstan-consistent-constructor
 */
abstract class AbstractData implements Arrayable
{
    /** @var TModel|null */
    private ?Model $eloquentModel = null;

    private int|string|null $morphAlias = null;

    private bool $existsInDatabase = false;

    /**
     * @return Factory<TModel>|null
     */
    public static function getFactory(): ?Factory
    {
        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $with
     */
    public static function fake(array $attributes = [], array $with = [], bool $persist = true): static
    {
        $factory = static::getFactory();

        if (null === $factory) {
            throw new RuntimeException('The getFactory method must be implemented.');
        }

        if (true === $persist) {
            $model = $factory->create($attributes);

            return self::fromEloquentModel($model->load($with))
                ->setExistsInDatabase(true)
                ->setEloquentModel($model);
        }

        $model = $factory->make($attributes);

        return self::fromEloquentModel($model)
            ->setEloquentModel($model);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $with
     * @return Collection<int, static>
     */
    public static function fakeCollection(
        array $attributes = [],
        int $count = 2,
        array $with = [],
        bool $persist = true,
    ): Collection {
        $factory = static::getFactory();

        if (null === $factory) {
            throw new RuntimeException('The getFactory method must be implemented.');
        }

        if (true === $persist) {
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

        /** @phpstan-ignore-next-line */
        return new static(...$properties);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property) use ($item): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return [$property->name => $name::fromDataArray($item)];
            })->toArray();

        /** @phpstan-ignore-next-line */
        return new static(...$properties);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidation(array $validated, ?self $data = null): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property) use ($validated, $data): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return [$property->name => $name::fromValidation($validated, $data)];
            })->toArray();

        /** @phpstan-var static<TModel> $instance */
        $instance = new static(...$properties);

        return $instance;
    }

    /**
     * @param  TModel|null  $model
     */
    public static function fromEloquentModel(?Model $model): static
    {
        if (null === $model) {
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

        return new static(...$properties)
            ->setEloquentModel($model)
            ->setMorphAlias($model->getMorphClass());
    }

    public static function fromData(AbstractData $data, string $method = 'fromData', mixed $options = null): static
    {
        return static::fromCustom($data, $method, $options);
    }

    public static function fromCustom(mixed $data, ?string $method = null, mixed $options = null): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property) use ($data, $method, $options): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                if (false === method_exists($name, $method)) {
                    return [$property->name => $name::new()];
                }

                return [$property->name => $name::$method($data, $options)];
            })->toArray();

        /** @phpstan-var static<TModel> $instance */
        $instance = new static(...$properties);

        return $instance;
    }

    public static function fromStandardClass(stdClass $data, mixed $options = null): static
    {
        $class = new ReflectionClass(static::class);

        $properties = collect($class->getProperties())
            ->mapWithKeys(function (ReflectionProperty $property) use ($data, $options): array {
                $method = 'fromStandardClass';

                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var class-string<AbstractDataValue> $name */
                $name = $type->getName();

                if (false === method_exists($name, $method)) {
                    return [$property->name => $name::new()];
                }

                return [$property->name => $name::$method($data, $options)];
            })->toArray();

        /** @phpstan-var static<TModel> $instance */
        $instance = new static(...$properties);

        return $instance;
    }

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $items
     * @return Collection<int, static>
     */
    public static function collect(Collection|array $items, string $method = 'fromData'): Collection
    {
        if (is_array($items)) {
            $items = collect($items);
        }

        return $items->map(fn (mixed $item) => static::from($item, $method));
    }

    public static function from(mixed $item, ?string $method = null, mixed $options = null): static
    {
        if ($item instanceof Model) {
            return static::fromEloquentModel($item);
        }

        if (is_array($item)) {
            return static::fromArray($item);
        }

        if ($item instanceof AbstractData) {
            return static::fromData($item, $method, $options);
        }

        if ($item instanceof stdClass) {
            return static::fromStandardClass($item, $options);
        }

        return static::fromCustom($item, $method, $options);
    }

    public static function getRelationName(): ?string
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function forEloquentFactory(): array
    {
        $class = new ReflectionClass(static::class);

        return collect($class->getProperties())
            ->filter(function (ReflectionProperty $property): bool {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return $name::$hasDBColumn && $name::$includeInEloquentFactory;

            })
            ->mapWithKeys(function (ReflectionProperty $property): array {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return [$name::getDatabaseTableColumnName() => $name::forEloquentFactoryValue()];
            })->toArray();
    }

    public function getPrimaryKey(): AbstractValue
    {
        throw new RuntimeException('The getPrimaryKey method must be implemented.');
    }

    public function getMorphAlias(): int|string|null
    {
        return $this->morphAlias;
    }

    /**
     * @return $this
     */
    public function setMorphAlias(int|string|null $morphAlias): AbstractData
    {
        $this->morphAlias = $morphAlias;

        return $this;
    }

    /**
     * Get the Eloquent model instance.
     *
     * @return TModel|null
     */
    public function getEloquentModel(): ?Model
    {
        return $this->eloquentModel;
    }

    /**
     * Set the Eloquent model instance.
     *
     * @param  TModel  $eloquentModel
     */
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
        return false === $this->isNull();
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function setExistsInDatabase(bool $existsInDatabase): static
    {
        $this->existsInDatabase = $existsInDatabase;

        return $this;
    }

    public function isExistsInDatabase(): bool
    {
        return $this->existsInDatabase;
    }

    public function notExistsInDatabase(): bool
    {
        return false === $this->isExistsInDatabase();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $class = new ReflectionClass(static::class);

        $data = collect($class->getProperties())
            ->filter(function (ReflectionProperty $property): bool {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return $name::$includeInArray;
            })
            ->mapWithKeys(function (ReflectionProperty $property): array {
                $propertyName = $property->name;

                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $typeName */
                $typeName = $type->getName();

                return [$typeName::getName() => $typeName::forArrayValue($this->{$propertyName}, $this)];
            })->toArray();

        $data['class'] = static::class;

        return $data;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toNullableArray(): ?array
    {
        return $this->isNull() ? null : $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function forDatabaseCreate(): array
    {
        $class = new ReflectionClass(static::class);

        return collect($class->getProperties())
            ->filter(function (ReflectionProperty $property): bool {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return $name::$hasDBColumn && $name::$includeInDatabaseCreate;
            })
            ->mapWithKeys(function (ReflectionProperty $property): array {
                $propertyName = $property->name;

                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $typeName */
                $typeName = $type->getName();

                return [$typeName::getDatabaseTableColumnName() => $typeName::forDatabaseCreateValue($this->{$propertyName}, $this)];
            })->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function forDatabaseUpdate(): array
    {
        $class = new ReflectionClass(static::class);

        return collect($class->getProperties())
            ->filter(function (ReflectionProperty $property): bool {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return $name::$hasDBColumn && $name::$includeInDatabaseUpdate;
            })
            ->mapWithKeys(function (ReflectionProperty $property): array {
                $propertyName = $property->name;

                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $typeName */
                $typeName = $type->getName();

                return [$typeName::getDatabaseTableColumnName() => $typeName::forDatabaseUpdateValue($this->{$propertyName}, $this)];
            })->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toEncryptedArray(): array
    {
        $class = new ReflectionClass(static::class);

        $data = collect($class->getProperties())
            ->filter(function (ReflectionProperty $property): bool {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                return $name::$includeInArray;
            })
            ->mapWithKeys(function (ReflectionProperty $property): array {
                $propertyName = $property->name;

                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $typeName */
                $typeName = $type->getName();

                return [$typeName::getName() => $typeName::forEncryptedArrayValue($this->{$propertyName}, $this)];
            })->toArray();

        $data['class'] = static::class;

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function forResource(Request $request): array
    {
        $fields = $request->array('fields');
        $class = new ReflectionClass(static::class);
        $prefix = mb_strtolower(Str::snake(Str::afterLast($class->getName(), '\\')));

        /** @var array<string, mixed> $data */
        $data = collect($class->getProperties())
            ->filter(function (ReflectionProperty $property) use ($fields, $prefix) {
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                if (count($fields) > 0) {
                    return $name::$includeInResponse && (in_array($name::getName(), $fields) || array_key_exists($name::getName(), $fields) || array_key_exists($prefix . '_' . $name::getName(), $fields));
                }

                return $name::$includeInResponse;
            })
            ->mapWithKeys(function (ReflectionProperty $property) use ($request, $fields, $prefix): array {
                $propertyName = $property->name;

                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $name */
                $name = $type->getName();

                if (count($fields) > 0 && (array_key_exists($name::getName(), $fields) || array_key_exists($prefix . '_' . $name::getName(), $fields))) {
                    $request = new Request(
                        query: [
                            'fields' => $fields[$name::getName()] ?? $fields[$prefix . '_' . $name::getName()],
                        ]
                    );
                }

                /** @var AbstractValue $value */
                $value = $this->{$propertyName};

                return [$name::getName() => $name::forResourceValue($value, $request)];
            })->toArray();

        if (in_array('class', $fields) || 0 === count($fields)) {
            $data['class'] = self::class;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return $this
     */
    public function setPropertyIfExists(array $values): static
    {
        $class = new ReflectionClass(self::class);

        collect($class->getProperties())
            ->each(function (ReflectionProperty $property) use ($values): void {
                $propertyName = Str::studly($property->name);
                /** @var ReflectionNamedType $type */
                $type = $property->getType();

                /** @var AbstractValue $class */
                $class = $type->getName();
                $name = $class::getName();

                $method = "set{$propertyName}";

                if (method_exists($this, $method) && array_key_exists($name, $values)) {
                    $this->{$method}($values[$name]);
                }
            });

        return $this;
    }

    public function toResource(): JsonResource
    {
        throw new RuntimeException('The toResource method must be implemented.');
    }

    protected static function resolveColumnName(string $className, string $fallback): string
    {
        return method_exists($className, 'getDatabaseTableColumnName')
            ? $className::getDatabaseTableColumnName()
            : (method_exists($className, 'getName')
                ? $className::getName()
                : $fallback);
    }
}

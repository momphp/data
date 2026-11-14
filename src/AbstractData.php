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
 */
abstract class AbstractData implements Arrayable
{
    /** @var TModel|null */
    private ?Model $eloquentModel = null;

    private int|string|null $morphAlias = null;

    private bool $existsInDatabase = false;

    /** @var array<class-string, array<string, array{0: ReflectionProperty, 1: class-string<AbstractValue>}>> */
    private static array $valuePropertyCache = [];

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

            if ([] !== $with) {
                $models->load($with);
            }

            return $models
                ->map(fn (Model $model): static => static::fromEloquentModel($model)->setExistsInDatabase(true))
                ->values();
        }

        return $factory->count($count)->make($attributes)
            ->map(fn (Model $model): static => static::fromEloquentModel($model))
            ->values();
    }

    public static function new(): static
    {
        $properties = [];

        foreach (static::valueProperties() as $name => [, $valueClass]) {
            $properties[$name] = $valueClass::new();
        }

        return new static(...$properties); // @phpstan-ignore new.static, return.type
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): static
    {
        $properties = [];

        foreach (static::valueProperties() as $name => [, $valueClass]) {
            $properties[$name] = $valueClass::fromDataArray($item);
        }

        return new static(...$properties); // @phpstan-ignore new.static, return.type
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidation(array $validated, ?self $data = null): static
    {
        $properties = [];

        foreach (static::valueProperties() as $name => [, $valueClass]) {
            $properties[$name] = $valueClass::fromValidation($validated, $data);
        }

        return new static(...$properties); // @phpstan-ignore new.static, return.type
    }

    /**
     * @param  TModel|null  $model
     */
    public static function fromEloquentModel(?Model $model): static
    {
        if (null === $model) {
            return static::new();
        }

        $properties = [];

        foreach (static::valueProperties() as $name => [, $valueClass]) {
            $properties[$name] = $valueClass::fromEloquentModel($model);
        }

        return new static(...$properties) // @phpstan-ignore new.static
            ->setEloquentModel($model)
            ->setMorphAlias($model->getMorphClass());
    }

    public static function fromData(AbstractData $data, string $method = 'fromData', mixed $options = null): static
    {
        return static::fromCustom($data, $method, $options);
    }

    public static function fromCustom(mixed $data, ?string $method = null, mixed $options = null): static
    {
        $properties = [];

        foreach (static::valueProperties() as $name => [, $valueClass]) {
            $properties[$name] = null === $method || false === method_exists($valueClass, $method)
                ? $valueClass::new()
                : $valueClass::$method($data, $options);
        }

        return new static(...$properties); // @phpstan-ignore new.static, return.type
    }

    public static function fromStandardClass(stdClass $data, mixed $options = null): static
    {
        return static::fromCustom($data, 'fromStandardClass', $options);
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
            return static::fromData($item, $method ?? 'fromData', $options);
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
        $attributes = [];

        foreach (static::valueProperties() as [, $valueClass]) {
            if ($valueClass::$hasDBColumn && $valueClass::$includeInEloquentFactory) {
                $attributes[$valueClass::getDatabaseTableColumnName()] = $valueClass::forEloquentFactoryValue();
            }
        }

        return $attributes;
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
        return json_encode($this->toArray(), $options | JSON_THROW_ON_ERROR);
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
        $data = [];

        foreach (static::valueProperties() as [$property, $valueClass]) {
            if ($valueClass::$includeInArray) {
                $data[$valueClass::getName()] = $valueClass::forArrayValue($property->getValue($this), $this);
            }
        }

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
        $data = [];

        foreach (static::valueProperties() as [$property, $valueClass]) {
            if ($valueClass::$hasDBColumn && $valueClass::$includeInDatabaseCreate) {
                $data[$valueClass::getDatabaseTableColumnName()] = $valueClass::forDatabaseCreateValue($property->getValue($this), $this);
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function forDatabaseUpdate(): array
    {
        $data = [];

        foreach (static::valueProperties() as [$property, $valueClass]) {
            if ($valueClass::$hasDBColumn && $valueClass::$includeInDatabaseUpdate) {
                $data[$valueClass::getDatabaseTableColumnName()] = $valueClass::forDatabaseUpdateValue($property->getValue($this), $this);
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toEncryptedArray(): array
    {
        $data = [];

        foreach (static::valueProperties() as [$property, $valueClass]) {
            if ($valueClass::$includeInArray) {
                $data[$valueClass::getName()] = $valueClass::forEncryptedArrayValue($property->getValue($this), $this);
            }
        }

        $data['class'] = static::class;

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function forResource(Request $request): array
    {
        $fields = $request->array('fields');
        $hasFields = count($fields) > 0;
        $prefix = mb_strtolower(Str::snake(Str::afterLast(static::class, '\\')));

        /** @var array<string, mixed> $data */
        $data = [];

        foreach (static::valueProperties() as [$property, $valueClass]) {
            if ( ! $valueClass::$includeInResponse) {
                continue;
            }

            $name = $valueClass::getName();
            $prefixedName = $prefix . '_' . $name;
            $hasNestedFields = $hasFields && (array_key_exists($name, $fields) || array_key_exists($prefixedName, $fields));

            if ($hasFields && ! $hasNestedFields && ! in_array($name, $fields)) {
                continue;
            }

            // Nested field selections are passed down to the value as their own request.
            $valueRequest = $hasNestedFields
                ? new Request(query: ['fields' => $fields[$name] ?? $fields[$prefixedName]])
                : $request;

            /** @var AbstractValue $value */
            $value = $property->getValue($this);

            $data[$name] = $valueClass::forResourceValue($value, $valueRequest);
        }

        if (in_array('class', $fields) || ! $hasFields) {
            $data['class'] = static::class;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return $this
     */
    public function setPropertyIfExists(array $values): static
    {
        foreach (static::valueProperties() as $propertyName => [, $valueClass]) {
            $name = $valueClass::getName();
            $method = 'set' . Str::studly($propertyName);

            if (array_key_exists($name, $values) && method_exists($this, $method)) {
                $this->{$method}($values[$name]);
            }
        }

        return $this;
    }

    public function toResource(): JsonResource
    {
        throw new RuntimeException('The toResource method must be implemented.');
    }

    /**
     * The value properties of this data class, keyed by property name.
     *
     * Reflection is done once per class and cached for the rest of the process.
     *
     * @return array<string, array{0: ReflectionProperty, 1: class-string<AbstractValue>}>
     */
    protected static function valueProperties(): array
    {
        if (isset(self::$valuePropertyCache[static::class])) {
            return self::$valuePropertyCache[static::class];
        }

        $properties = [];

        foreach (new ReflectionClass(static::class)->getProperties() as $property) {
            /** @var ReflectionNamedType $type */
            $type = $property->getType();

            /** @var class-string<AbstractValue> $valueClass */
            $valueClass = $type->getName();

            $properties[$property->name] = [$property, $valueClass];
        }

        return self::$valuePropertyCache[static::class] = $properties;
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

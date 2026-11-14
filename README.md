# mom/data

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mom/data.svg?style=flat-square)](https://packagist.org/packages/mom/data)
[![Total Downloads](https://img.shields.io/packagist/dt/mom/data.svg?style=flat-square)](https://packagist.org/packages/mom/data)

Typed data objects for Laravel. Each field is its own small class that knows its name, its database column and how to convert itself, so one data class can be built from arrays, Eloquent models, validated requests and other data, and turned back into arrays, database rows, API resources and JSON.

```php
$user = User::fromEloquentModel(UserModel::find($id));

$user->getEmail()->toString();       // "ada@example.com"
$user->getCreatedAt()->toCarbon();   // CarbonImmutable
$user->toArray();                    // ['uuid' => '…', 'email' => '…', 'created_at' => '2026-…Z', 'class' => User::class]
$user->forDatabaseUpdate();          // ['id' => '…', 'email' => '…', 'created_at' => '2026-… …']
```

## Requirements

- PHP 8.4+
- Laravel 13.30+

## Installation

```bash
composer require mom/data
```

## Quick start

### 1. Define a value class for each field

Extend the base class that matches the field's type and return the field's name. The name is used as the array key and, by default, as the database column.

```php
namespace App\User\Properties;

use Mom\Data\AbstractString;

class Email extends AbstractString
{
    public static function getName(): string
    {
        return 'email';
    }
}
```

If the database column has a different name, override `getDatabaseTableColumnName()`:

```php
namespace App\User\Properties;

use Mom\Data\AbstractString;

class Uuid extends AbstractString
{
    public static function getName(): string
    {
        return 'uuid';
    }

    public static function getDatabaseTableColumnName(): string
    {
        return 'id';
    }
}
```

### 2. Define the data class

Extend `AbstractData` and declare each value class as a constructor property. Every property must be typed with a value class, and **constructor parameter names must match the property names**, because the package builds instances with named arguments.

```php
namespace App\User;

use App\User\Properties\CreatedAt;
use App\User\Properties\Email;
use App\User\Properties\Uuid;
use Mom\Data\AbstractData;
use Mom\Data\AbstractValue;

class User extends AbstractData
{
    public function __construct(
        private Uuid $uuid,
        private Email $email,
        private CreatedAt $createdAt,
    ) {}

    public function getPrimaryKey(): AbstractValue
    {
        return $this->uuid;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(mixed $email): static
    {
        $this->email = new Email($email);

        return $this;
    }

    public function getCreatedAt(): CreatedAt
    {
        return $this->createdAt;
    }
}
```

Properties can be public, protected or private.

### 3. Use it

```php
$user = User::fromArray([
    'uuid' => '123e4567-e89b-12d3-a456-426614174000',
    'email' => 'ada@example.com',
    'created_at' => '2026-01-02 03:04:05',
]);

$user->getEmail()->toString();                  // "ada@example.com"
$user->getCreatedAt()->toDateString();          // "2026-01-02"
$user->setEmail('grace@example.com')->toArray();
```

## Value types

| Base class | Holds | Read it with | Notes |
|---|---|---|---|
| `AbstractString` | `string` | `toString()`, `toNullableString()` | Also accepts `UuidInterface` |
| `AbstractInteger` | `int` | `toInteger()`, `toNullableInteger()` | Accepts integer strings such as `"42"` |
| `AbstractFloat` | `float` | `toFloat()`, `toNullableFloat()` | Accepts ints and numeric strings |
| `AbstractBoolean` | `bool` | `toBoolean()`, `toNullableBoolean()` | Accepts `1`/`0`, `"true"`/`"false"`, `"yes"`/`"no"` and `"on"`/`"off"`. You must implement `default()` |
| `AbstractDate` | `CarbonImmutable` | `toCarbon()`, `toNullableCarbon()`, `toISOString()`, `toDateTimeString()`, `toDateString()` | Accepts date strings, Carbon and `DateTime`. An empty string is `null` |
| `AbstractCollection` | `Collection` | `toCollection()`, `toArray()` | Decodes JSON array columns |
| `AbstractEnum` | `BackedEnum` | `toEnum()`, `toNullableEnum()`, `toString()`, `toInteger()` | You must implement `toNullableEnum()`, `toEnum()` and `forEloquentFactoryValue()` |
| `AbstractDataValue` | another `AbstractData` | `toData()`, `toNullableData()` | For nested data. You must implement `toNullableData()` and `toData()` |

Every value also has `isNull()`, `isNotNull()`, `toValue()` and `equalsTo()`.

Methods without `Nullable` in their name fall back to a default when the value is missing:

| Method | Default |
|---|---|
| `toString()` | `''` |
| `toInteger()` | `0` |
| `toFloat()` | `0.0` |
| `toCollection()` | an empty collection |
| `toBoolean()` | your `default()` |
| `toCarbon()` | **now** |

Use the `toNullable…()` variants whenever "missing" has to stay distinguishable.

### Booleans

```php
use Mom\Data\AbstractBoolean;

class IsAdmin extends AbstractBoolean
{
    public static function getName(): string
    {
        return 'is_admin';
    }

    public function default(): bool
    {
        return false;
    }
}
```

### Enums

```php
use Mom\Data\AbstractEnum;

enum Status: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}

class UserStatus extends AbstractEnum
{
    public static function getName(): string
    {
        return 'status';
    }

    public static function forEloquentFactoryValue(): string
    {
        return Status::Active->value;
    }

    public function toNullableEnum(): ?Status
    {
        $value = $this->toValue();

        return $value instanceof Status ? $value : Status::tryFrom((string) $value);
    }

    public function toEnum(): Status
    {
        return $this->toNullableEnum() ?? Status::Active;
    }
}
```

### Nested data

Use `AbstractDataValue` when a field holds another data object. The raw value may be an array (including a decoded JSON column), a data object, or a closure for lazy loading.

```php
use Mom\Data\AbstractDataValue;

class Address extends AbstractDataValue
{
    public static function getName(): string
    {
        return 'address';
    }

    public function toNullableData(): ?AddressData
    {
        $value = $this->toValue();

        return match (true) {
            $value instanceof AddressData => $value,
            is_array($value) => AddressData::fromArray($value),
            default => null,
        };
    }

    public function toData(): AddressData
    {
        return $this->toNullableData() ?? AddressData::new();
    }
}
```

A value created with `Address::fromClosure(fn () => …)` runs the closure the first time it's read and reuses the result after that.

## Creating data objects

| Method | Reads values from |
|---|---|
| `User::new()` | Nothing. Every field is empty |
| `User::fromArray($array)` | Keys matching each field's `getName()` |
| `User::fromEloquentModel($model)` | The model's raw attributes, by `getDatabaseTableColumnName()`. Also keeps the model (`getEloquentModel()`) |
| `User::fromValidation($request->validated())` | Keys matching each field's `getNameForValidation()` |
| `User::fromStandardClass($object)` | A `stdClass`, by calling `fromStandardClass()` on each value class that defines it |
| `User::from($anything)` | Picks one of the methods above based on the input type |
| `User::collect($items)` | A collection or array of any of the above. Returns a `Collection` of `User` |

Fields whose value class doesn't define the method `from()` dispatches to (for example `fromStandardClass`) are left empty.

```php
$users = User::collect(UserModel::query()->latest()->get());
```

### How database values are read

`fromEloquentModel()` reads the model's **raw** attributes, so casts are not applied.

- **Strings stay strings.** A string column holding `"123"`, `"true"` or `"null"` is not converted.
- **JSON is decoded only for collections and nested data**, and only when it decodes to an array.
- **Numbers that arrive as strings** (as with some MySQL drivers) are read correctly by `AbstractInteger` and `AbstractFloat`.

To change how one field reads its column, override `castDatabaseValue()` on its value class.

## Converting data objects

| Method | Keys | Notes |
|---|---|---|
| `toArray()` | `getName()` | Dates as ISO 8601 strings, plus a `class` key |
| `toJson()` | `getName()` | `toArray()` as JSON. Throws `JsonException` on failure |
| `forDatabaseCreate()` | `getDatabaseTableColumnName()` | Dates as `Y-m-d H:i:s`, ready for `Model::create()` |
| `forDatabaseUpdate()` | `getDatabaseTableColumnName()` | Ready for `$model->update()` |
| `forResource($request)` | `getName()` | For API responses, with optional field selection (see below) |
| `toEncryptedArray()` | `getName()` | Values encrypted with Laravel's encrypter (booleans are left as they are) |

```php
UserModel::query()->create($user->forDatabaseCreate());
```

### Choosing which fields are included

Each value class has static flags you can override:

```php
class Password extends AbstractString
{
    public static bool $includeInArray = false;      // toArray(), toEncryptedArray()
    public static bool $includeInResponse = false;   // forResource()

    public static function getName(): string
    {
        return 'password';
    }
}
```

| Flag | Default | Controls |
|---|---|---|
| `$includeInArray` | `true` | `toArray()`, `toEncryptedArray()` |
| `$includeInResponse` | `true` | `forResource()` |
| `$includeInDatabaseCreate` | `true` | `forDatabaseCreate()` |
| `$includeInDatabaseUpdate` | `true` | `forDatabaseUpdate()` |
| `$includeInEloquentFactory` | `true` | `forEloquentFactory()` |
| `$hasDBColumn` | `true` | Set to `false` for computed fields. They're left out of all database and factory output |

### API resources and field selection

`forResource()` returns every field with `$includeInResponse`, plus `class`. If the request has a `fields` query parameter, only those fields are returned:

```
GET /users/1?fields[]=email&fields[]=created_at
```

```php
$user->forResource($request);   // ['email' => '…', 'created_at' => '…']
```

Add `class` to the list to include the `class` key. To choose fields of a nested collection or data value, pass them under that field's name, or under the data class's snake-cased name as a prefix (`user_roles`):

```
GET /users/1?fields[roles][]=name&fields[]=email
```

## Factories and fakes

Return an Eloquent factory from `getFactory()` to create fake data objects in tests:

```php
class User extends AbstractData
{
    public static function getFactory(): UserFactory
    {
        return UserModel::factory();
    }

    // …
}

$user = User::fake(['email' => 'ada@example.com']);                  // saved to the database
$draft = User::fake(persist: false);                                 // not saved
$users = User::fakeCollection(count: 3, with: ['roles']);            // relations eager-loaded

$user->isExistsInDatabase();   // true for saved fakes
```

`User::forEloquentFactory()` returns a fake value for every database column, keyed by column name. You can use it as a factory's `definition()`:

```php
class UserFactory extends Factory
{
    protected $model = UserModel::class;

    public function definition(): array
    {
        return User::forEloquentFactory();
    }
}
```

## Database column helpers

Value classes can supply column names in migrations and queries, so a renamed column only has to change in one place:

```php
Schema::create('users', function (Blueprint $table): void {
    $table->uuid(Uuid::getDatabaseTableColumnName())->primary();
    $table->string(Email::getDatabaseTableColumnName())->unique();
    $table->timestamp(CreatedAt::getDatabaseTableColumnName());
});

UserModel::query()->where(Email::getDatabaseTableColumnNameWithTable('users'), $email);   // "users.email"
UserModel::query()->select(Email::getAliasedColumnNameWithTableForSelect('users'));       // "users.email as users_email"
```

## Testing

```bash
composer test
```

## Contributing

This repository is a read-only split of [momphp/monorepo](https://github.com/momphp/monorepo). Please open issues and pull requests there.

## License

MIT. See [LICENSE.md](LICENSE.md).

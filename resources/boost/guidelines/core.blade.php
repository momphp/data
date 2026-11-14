@verbatim
## mom/data

`mom/data` provides typed data objects. A data class extends `Mom\Data\AbstractData`. Each field is its own value class extending one of the `Mom\Data\Abstract*` value types, and it knows its name, its database column and how to convert itself. Use data objects instead of passing raw arrays or Eloquent models between layers.

### Value classes

- Create one value class per field. Extend the base class that matches the type:

| Base class | Holds |
|---|---|
| `AbstractString` | `string` |
| `AbstractInteger` | `int` |
| `AbstractFloat` | `float` |
| `AbstractBoolean` | `bool` |
| `AbstractDate` | `CarbonImmutable` |
| `AbstractCollection` | `Collection` |
| `AbstractEnum` | `BackedEnum` |
| `AbstractDataValue` | another data object |

- Every value class implements `public static function getName(): string`. The name is the array key, the validation key and, by default, the database column.
- When the column name differs, override `getDatabaseTableColumnName()`. Don't rename the field instead.
- Some base classes need extra methods:
  - `AbstractBoolean` needs `default(): bool`.
  - `AbstractEnum` needs `toNullableEnum()`, `toEnum()` and `static forEloquentFactoryValue()`.
  - `AbstractDataValue` needs `toNullableData()` and `toData()`.
- To change how one field reads its raw database value, override `castDatabaseValue()`. Don't change how the data class reads the model.

```php
use Mom\Data\AbstractString;

class Email extends AbstractString
{
    public static function getName(): string
    {
        return 'email';
    }
}
```

### Data classes

- Declare every field as a constructor property typed with its value class. Every property of a data class must be a value class: no plain `string`, `int` or `array` properties.
- Constructor parameter names must match the property names, because instances are built with named arguments.
- Implement `getPrimaryKey(): AbstractValue`.
- Getters return the value object. Setters accept `mixed` and wrap the input in a new value object.

```php
use Mom\Data\AbstractData;
use Mom\Data\AbstractValue;

class User extends AbstractData
{
    public function __construct(
        private Uuid $uuid,
        private Email $email,
    ) {}

    public function getPrimaryKey(): AbstractValue
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
}
```

### Creating and converting

Use the package's methods instead of building arrays by hand.

**Create**
- `User::new()`: every field empty.
- `User::fromArray($array)`: keys are each field's `getName()`.
- `User::fromEloquentModel($model)`: reads raw attributes by `getDatabaseTableColumnName()`.
- `User::fromValidation($request->validated())`: reads keys by `getNameForValidation()`.
- `User::collect($models)`: a `Collection` of `User`.
- `User::from($anything)`: picks one of the methods above based on the input type.

**Convert**
- `toArray()` and `toJson()`: dates as ISO 8601 strings, plus a `class` key.
- `forDatabaseCreate()` and `forDatabaseUpdate()`: keyed by column name, ready for `Model::create()` and `$model->update()`.
- `forResource($request)`: for API responses. Supports `?fields[]=…` selection.

`fromEloquentModel()` reads raw attributes, so the model's casts are not applied. JSON is decoded only for `AbstractCollection` and `AbstractDataValue`.

### Reading values

- Read with the typed methods: `toString()`, `toInteger()`, `toFloat()`, `toBoolean()`, `toCarbon()`, `toCollection()`, `toEnum()` and `toData()`.
- Those methods return a default when the value is missing. `toCarbon()` returns **now**.
- Use the `toNullable…()` variants, or `isNull()` and `isNotNull()`, whenever "missing" must stay distinguishable from the default.
- Compare values with `equalsTo()`.

### Leaving fields out

Hide a field by overriding a static flag on its value class. Don't unset keys afterwards.
- `$includeInArray`
- `$includeInResponse`
- `$includeInDatabaseCreate`
- `$includeInDatabaseUpdate`
- `$includeInEloquentFactory`
- `$hasDBColumn = false`, for computed fields.

```php
class Password extends AbstractString
{
    public static bool $includeInArray = false;
    public static bool $includeInResponse = false;

    public static function getName(): string
    {
        return 'password';
    }
}
```

### Column names in queries and migrations

Use the value classes instead of string literals, so a renamed column changes in one place:

```php
$table->string(Email::getDatabaseTableColumnName())->unique();

UserModel::query()->where(Email::getDatabaseTableColumnNameWithTable('users'), $email);
```

### Tests

- When `getFactory()` returns the model's factory:
  - `User::fake($attributes)` creates and saves a model and returns the data object.
  - `User::fake(persist: false)` returns one without saving.
  - `User::fakeCollection(count: 3)` returns several.
- A factory's `definition()` can return `User::forEloquentFactory()`.
@endverbatim

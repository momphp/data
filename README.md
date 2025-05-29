# Minimalist Data Transfer Object (DTO) Package for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mom/data.svg?style=flat-square)](https://packagist.org/packages/mom/data)
[![Total Downloads](https://img.shields.io/packagist/dt/mom/data.svg?style=flat-square)](https://packagist.org/packages/mom/data)

A simple and flexible Data Transfer Object (DTO) package for PHP, allowing clean data encapsulation and validation.

## Features

- **Type-safe properties** - Define properties with specific types to ensure consistent data handling.
- **Automatic property assignment** - Easily map input data to DTO properties.

## Installation

Install the package via Composer:

```bash
composer require mom/data
```

## Getting Started

### Step 1: Create a property class

Define a property class and extend `AbstractString`, `AbstractInteger`, `AbstractFloat`, `AbstractCollection`, `AbstractDate`, `AbstractDataValue`, `AbstractEnum` or `AbstractBoolean` based on the property type:

```php
<?php

namespace App\User\Properties;

use Mom\Data\AbstractString;

class Uuid extends AbstractString
{
    public static function getName(): string
    {
        return 'uuid';
    }
}
```

### Step 2: Create a DTO class

Next, create DTO class and extend `AbstractData` class:

```php
<?php

namespace App\User;

use Mom\Data\AbstractData;
use App\User\Properties\Uuid;

class User extends AbstractData
{
    public function __construct(
        private Uuid $uuid,
    ) {}
    
    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
    
    public function setUuid(mixed $uuid): User
    {
        $this->uuid = new Uuid($uuid);
        
        return $this;
    }
}
```

### Step 3: Use the DTO class

Once created, you can access the DTO properties as usual:

```php
use App\User\User;

$data = [
    'uuid' => '123e4567-e89b-12d3-a456-426614174000',
];

$user = User::fromArray($data);

echo $user->getUuid()->toString(); // Outputs: 123e4567-e89b-12d3-a456-426614174000
```

## Laravel Eloquent Support

### Step 1: Create a property class

Define a property class and extend `AbstractString`, `AbstractInteger`, `AbstractFloat`, `AbstractCollection`, `AbstractDate`, `AbstractDataValue`, `AbstractEnum` or `AbstractBoolean` based on the property type:

```php
<?php

namespace App\User\Properties;

use Mom\Data\AbstractString;

class Uuid extends AbstractString
{
    public static function getName(): string
    {
        return 'uuid';
    }
    
    // Optionally, you can define the database column name if it is different from the getName()
    public static function getDatabaseTableColumnName() : string
    {
        return 'id' 
    }
}
```

### Step 2: Optionally, you can use it in your migrations

```php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Table::Users->value, function (Blueprint $table): void {
            $table->uuid(Uuid::getDatabaseTableColumnName())->primary();
            $table->string(FirstName::getDatabaseTableColumnName());
            $table->string(LastName::getDatabaseTableColumnName());
            $table->string(Email::getDatabaseTableColumnName())->unique();
            $table->string(Password::getDatabaseTableColumnName())->nullable();
        );
    }
}
```

### Step 2: Create a DTO class

Next, create DTO class and extend `AbstractData` class:

```php
<?php

namespace App\User;

use Mom\Data\AbstractData;
use App\User\Properties\Uuid;
use App\User\Properties\FirstName;
use App\User\Properties\LastName;
use App\User\Properties\Email;
use App\User\Properties\Password;

class User extends AbstractData
{
    public function __construct(
        private Uuid $uuid,
        private FirstName $firstName,
        private LastName $lastName,
        private Email $email
        private Password $password
    ) {}
    
    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
    
    public function setUuid(mixed $uuid): User
    {
        $this->uuid = new Uuid($uuid);
        
        return $this;
    }
    
    public function getFirstName(): Uuid
    {
        return $this->firstName;
    }
    
    public function setFirstName(mixed $uuid): User
    {
        $this->uuid = new FirstName($uuid);
        
        return $this;
    }
    
    ...
}
```

### Step 3: Use the DTO class

Once created, you can now access the typed Eloquent properties:

```php
use App\User\User;
use App\Models\User as UserEloquentModel

UserEloquentModel::query()->create([
    'uuid' => '123e4567-e89b-12d3-a456-426614174000'
    ... 
])

$model = UserEloquentModel::find('123e4567-e89b-12d3-a456-426614174000'); 

$user = User::fromEloquentModel($model);

echo $user->getUuid()->toString(); // Outputs: 123e4567-e89b-12d3-a456-426614174000
```

## Contributing

Contributions are welcome! Please submit issues or pull requests.

## License

This package is open-sourced software licensed under MIT license
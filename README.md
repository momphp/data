# Minimalist Data Transfer Object (DTO) Package for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mom/data.svg?style=flat-square)](https://packagist.org/packages/mom/data)
[![Total Downloads](https://img.shields.io/packagist/dt/mom/data.svg?style=flat-square)](https://packagist.org/packages/momphp/data)

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

Define a property class and extend `AbstractString`, `AbstractInteger`, `AbstractFloat`, `AbstractCollection` or `AbstractBoolean` based on the property type:

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
    
    public function setUuid(Uuid $uuid): User
    {
        $this->uuid = $uuid;
        
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

## Contributing

Contributions are welcome! Please submit issues or pull requests.

## License

This package is open-sourced software licensed under
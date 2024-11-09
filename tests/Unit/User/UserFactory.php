<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mom\Data\Tests\Unit\User\Properties\Email;

class UserFactory extends Factory
{
    protected $model = UserEloquentModel::class;

    public function definition(): array
    {
        return [
            Email::getDatabaseTableColumnName() => fake()->unique()->safeEmail(),
        ];
    }
}

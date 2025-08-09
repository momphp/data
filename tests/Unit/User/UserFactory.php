<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = UserEloquentModel::class;

    public function definition(): array
    {
        return User::new()->forEloquentFactory();
    }
}

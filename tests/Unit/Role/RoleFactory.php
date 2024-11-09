<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\Role;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = RoleEloquentModel::class;

    public function definition(): array
    {
        return [];
    }
}

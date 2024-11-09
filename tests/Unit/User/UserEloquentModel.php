<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEloquentModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}

<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\Role;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleEloquentModel extends Model
{
    use HasFactory;

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}

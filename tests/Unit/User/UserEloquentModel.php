<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserEloquentModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Other users of the same type, used to test eager loading in fakes.
     *
     * @return HasMany<UserEloquentModel, $this>
     */
    public function peers(): HasMany
    {
        return $this->hasMany(self::class, 'type', 'type');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}

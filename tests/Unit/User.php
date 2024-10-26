<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mom\Data\Data;
use Mom\Data\Value;

class User extends Data
{
    public static function getFactory(): Factory
    {
        // TODO: Implement getFactory() method.
    }

    public function getPrimaryKey(): Value
    {
        // TODO: Implement getPrimaryKey() method.
    }
}

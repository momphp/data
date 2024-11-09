<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\Role\Properties;

use Mom\Data\AbstractString;

class Name extends AbstractString
{
    public static function getName(): string
    {
        return 'name';
    }
}

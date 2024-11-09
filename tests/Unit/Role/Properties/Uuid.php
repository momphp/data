<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\Role\Properties;

use Mom\Data\AbstractString;

class Uuid extends AbstractString
{
    public static function getName(): string
    {
        return 'uuid';
    }
}

<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractCollection;

class Roles extends AbstractCollection
{
    protected static bool $isIncludeAllowed = true;

    public static function getName(): string
    {
        return 'roles';
    }
}

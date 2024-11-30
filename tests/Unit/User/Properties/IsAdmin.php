<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractBoolean;

class IsAdmin extends AbstractBoolean
{
    public static function getName(): string
    {
        return 'is_admin';
    }

    public function default(): bool
    {
        return false;
    }
}

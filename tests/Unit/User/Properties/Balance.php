<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractFloat;

class Balance extends AbstractFloat
{
    protected static bool $isVisible = false;

    public static function getName(): string
    {
        return 'balance';
    }
}

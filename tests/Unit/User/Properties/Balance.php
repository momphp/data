<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractFloat;

class Balance extends AbstractFloat
{
    public static function getName(): string
    {
        return 'balance';
    }
}

<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractInteger;

class Age extends AbstractInteger
{
    public static function getName(): string
    {
        return 'age';
    }
}

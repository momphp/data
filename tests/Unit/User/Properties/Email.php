<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractString;

class Email extends AbstractString
{
    public static function getName(): string
    {
        return 'email';
    }
}

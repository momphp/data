<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractDate;

class CreatedAt extends AbstractDate
{
    public static function getName(): string
    {
        return 'created_at';
    }
}

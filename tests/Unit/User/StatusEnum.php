<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User;

enum StatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

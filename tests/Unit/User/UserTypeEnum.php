<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User;

enum UserTypeEnum: int
{
    case User = 1;
    case Admin = 2;
    case SuperAdmin = 3;
}

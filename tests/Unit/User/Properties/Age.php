<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractInteger;

class Age extends AbstractInteger
{
    protected static bool $isVisible = false;

    protected static bool $isSortable = true;

    protected static bool $isFilterable = true;

    public static function getName(): string
    {
        return 'age';
    }
}

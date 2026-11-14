<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\Role;

use Mom\Data\AbstractData;
use Mom\Data\AbstractDataValue;

class RoleValue extends AbstractDataValue
{
    public static function getName(): string
    {
        return 'role';
    }

    public function toNullableData(): ?Role
    {
        $value = $this->toValue();

        if ($value instanceof Role) {
            return $value;
        }

        if (is_array($value)) {
            return Role::fromArray($value);
        }

        return null;
    }

    public function toData(): AbstractData
    {
        return $this->toNullableData() ?? Role::new();
    }
}

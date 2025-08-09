<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractEnum;
use Mom\Data\Tests\Unit\User\UserTypeEnum;

class Type extends AbstractEnum
{
    public static function getName(): string
    {
        return 'type';
    }

    public function toNullableEnum(): ?UserTypeEnum
    {
        $value = $this->toValue();

        if ($value instanceof UserTypeEnum) {
            return $value;
        }

        if (is_int($value)) {
            return UserTypeEnum::tryFrom($value);
        }

        return null;
    }

    public function toEnum(): UserTypeEnum
    {
        return $this->toNullableEnum() ?? UserTypeEnum::Admin;
    }
}

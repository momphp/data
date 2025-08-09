<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User\Properties;

use Mom\Data\AbstractEnum;
use Mom\Data\Tests\Unit\User\StatusEnum;

class Status extends AbstractEnum
{
    public static function getName(): string
    {
        return 'status';
    }

    public function toNullableEnum(): ?StatusEnum
    {
        $value = $this->toValue();

        if ($value instanceof StatusEnum) {
            return $value;
        }

        if (is_string($value)) {
            return StatusEnum::tryFrom($value);
        }

        return null;
    }

    public function toEnum(): StatusEnum
    {
        return $this->toNullableEnum() ?? StatusEnum::Active;
    }
}

<?php

declare(strict_types=1);

namespace Mom\Data;

abstract class AbstractString extends AbstractValue
{
    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public static function fromNullableString(?string $value): static
    {
        return new static($value);
    }

    public function toPrimitive(): ?string
    {
        return $this->toNullableString();
    }

    public function toNullableString(): ?string
    {
        $value = $this->toValue();

        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    public function toString(): string
    {
        return $this->toNullableString() ?? '';
    }
}

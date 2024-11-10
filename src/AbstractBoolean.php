<?php

declare(strict_types=1);

namespace Mom\Data;

abstract class AbstractBoolean extends AbstractValue
{
    abstract public function toBoolean(): bool;

    public static function fromBoolean(bool $value): static
    {
        return new static($value);
    }

    public static function fromNullableBoolean(?bool $value): static
    {
        return new static($value);
    }

    public function toPrimitive(): ?bool
    {
        return $this->toNullableBoolean();
    }

    public function toNullableBoolean(): ?bool
    {
        $value = $this->toValue();

        if (is_bool($value)) {
            return $value;
        }

        if ('1' === $value || 1 === $value || 'true' === $value || 'yes' === $value || 'on' === $value) {
            return true;
        }

        if ('0' === $value || 0 === $value || 'false' === $value || 'no' === $value || 'off' === $value) {
            return false;
        }

        return null;
    }
}

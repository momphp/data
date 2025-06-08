<?php

declare(strict_types=1);

use Mom\Data\AbstractEnum;

enum StringEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
    case Unknown = 'unknown';
}

class Status extends AbstractEnum
{
    public static function getName(): string
    {
        return 'status';
    }

    public function toNullableEnum(): ?BackedEnum
    {
        $value = $this->toValue();

        if (null === $value) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value;
        }

        return StringEnum::tryFrom($value);
    }

    public function toEnum(): BackedEnum
    {
        return $this->toNullableEnum() ?? StringEnum::Unknown;
    }
}

it('can return string for string backed enum', function (): void {
    $status = Status::fromEnum(StringEnum::Foo);

    expect($status->toString())
        ->toBe('foo');
});

enum IntEnum: int
{
    case One = 1;
    case Two = 2;
    case Unknown = 3;
}

class Number extends AbstractEnum
{
    public static function getName(): string
    {
        return 'number';
    }

    public function toNullableEnum(): ?BackedEnum
    {
        $value = $this->toValue();

        if (null === $value) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value;
        }

        return IntEnum::tryFrom($value);
    }

    public function toEnum(): BackedEnum
    {
        return $this->toNullableEnum() ?? IntEnum::Unknown;
    }
}

it('can return string for int backed enum', function (): void {
    $status = Number::fromEnum(IntEnum::One);

    expect($status->toInteger())
        ->toBe(1);
});

<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\Status;
use Mom\Data\Tests\Unit\User\Properties\Type;
use Mom\Data\Tests\Unit\User\UserTypeEnum;

test('toNullableInteger returns null for a null enum', function (): void {
    expect(Type::new()->toNullableInteger())->toBeNull()
        ->and(Status::new()->toNullableInteger())->toBeNull();
});

test('toNullableInteger returns the backing value', function (): void {
    expect(Type::fromEnum(UserTypeEnum::Admin)->toNullableInteger())->toBe(2);
});

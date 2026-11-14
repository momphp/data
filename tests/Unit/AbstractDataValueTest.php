<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\Role\Role;
use Mom\Data\Tests\Unit\Role\RoleValue;

test('toPrimitive returns null without data', function (): void {
    expect(RoleValue::new()->toPrimitive())->toBeNull();
});

test('toPrimitive returns the data as an array', function (): void {
    $role = RoleValue::from(['name' => 'admin', 'uuid' => 'abc']);

    expect($role->toPrimitive())
        ->toMatchArray(['name' => 'admin', 'uuid' => 'abc', 'class' => Role::class]);
});

test('toNullableJson encodes the data once', function (): void {
    expect(RoleValue::new()->toNullableJson())->toBeNull()
        ->and(json_decode(RoleValue::from(['name' => 'admin'])->toNullableJson(), true))
        ->toMatchArray(['name' => 'admin']);
});

test('lazy closure values run once', function (): void {
    $calls = 0;
    $role = RoleValue::fromClosure(function () use (&$calls): array {
        $calls++;

        return ['name' => 'admin', 'uuid' => 'abc'];
    });

    $role->isNull();
    $role->toNullableArray();
    $role->toPrimitive();

    expect($calls)->toBe(1);
});

<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\Role\Role;
use Mom\Data\Tests\Unit\Role\RoleValue;
use Mom\Data\Tests\Unit\User\Properties\Age;
use Mom\Data\Tests\Unit\User\Properties\Balance;
use Mom\Data\Tests\Unit\User\Properties\Email;
use Mom\Data\Tests\Unit\User\Properties\Roles;
use Mom\Data\Tests\Unit\User\UserEloquentModel;

function modelWithAttributes(array $attributes): UserEloquentModel
{
    $model = new UserEloquentModel;
    $model->setRawAttributes($attributes);

    return $model;
}

test('string columns that look like JSON stay strings', function (string $raw): void {
    $email = Email::fromEloquentModel(modelWithAttributes(['email' => $raw]));

    expect($email->toNullableString())->toBe($raw);
})->with(['123', 'true', 'null', '"quoted"']);

test('collection columns decode JSON arrays', function (): void {
    $roles = Roles::fromEloquentModel(modelWithAttributes(['roles' => '["admin","user"]']));

    expect($roles->toArray())->toBe(['admin', 'user']);
});

test('collection columns keep non-array JSON as a string', function (): void {
    $roles = Roles::fromEloquentModel(modelWithAttributes(['roles' => '123']));

    expect($roles->toArray())->toBe(['123']);
});

test('data value columns decode JSON arrays', function (): void {
    $role = RoleValue::fromEloquentModel(modelWithAttributes(['role' => '{"name":"admin","uuid":"abc"}']));

    expect($role->toNullableData())
        ->toBeInstanceOf(Role::class)
        ->and($role->toNullableData()->getName()->toString())
        ->toBe('admin');
});

test('numeric columns returned as strings are read as numbers', function (): void {
    $model = modelWithAttributes(['age' => '42', 'balance' => '12.50']);

    expect(Age::fromEloquentModel($model)->toNullableInteger())
        ->toBe(42)
        ->and(Balance::fromEloquentModel($model)->toNullableFloat())
        ->toBe(12.5);
});

<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\Age;
use Mom\Data\Tests\Unit\User\Properties\Balance;
use Mom\Data\Tests\Unit\User\Properties\CreatedAt;
use Mom\Data\Tests\Unit\User\Properties\Email;
use Mom\Data\Tests\Unit\User\Properties\Status;
use Mom\Data\Tests\Unit\User\Properties\Type;
use Mom\Data\Tests\Unit\User\StatusEnum;
use Mom\Data\Tests\Unit\User\User;
use Mom\Data\Tests\Unit\User\UserTypeEnum;

test('fake method', function (): void {
    $age = fake()->randomNumber();
    $balance = fake()->randomFloat();
    $createdAt = now()->toDateTimeString();
    $email = fake()->safeEmail();
    $status = StatusEnum::Active;
    $type = UserTypeEnum::Admin;

    $user = User::fake(
        attributes: [
            Age::getDatabaseTableColumnName() => $age,
            Balance::getDatabaseTableColumnName() => $balance,
            CreatedAt::getDatabaseTableColumnName() => $createdAt,
            Email::getDatabaseTableColumnName() => $email,
            Status::getDatabaseTableColumnName() => $status->value,
            Type::getDatabaseTableColumnName() => $type->value,
        ],
        persist: false,
    );

    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->getAge()->toInteger())
        ->toBe($age)
        ->and($user->getBalance()->toFloat())
        ->toBe($balance)
        ->and($user->getCreatedAt()->toDateTimeString())
        ->toBe($createdAt)
        ->and($user->getEmail()->toString())
        ->toBe($email)
        ->and($user->getStatus()->toEnum())
        ->toBe($status)
        ->and($user->getType()->toEnum())
        ->toBe($type);
});

test('toArray method', function (): void {
    $age = fake()->randomNumber();
    $balance = fake()->randomFloat();
    $createdAt = now();
    $email = fake()->safeEmail();
    $status = StatusEnum::Active;
    $type = UserTypeEnum::Admin;

    $user = User::fake(
        attributes: [
            Age::getDatabaseTableColumnName() => $age,
            Balance::getDatabaseTableColumnName() => $balance,
            CreatedAt::getDatabaseTableColumnName() => $createdAt,
            Email::getDatabaseTableColumnName() => $email,
            Status::getDatabaseTableColumnName() => $status->value,
            Type::getDatabaseTableColumnName() => $type->value,
        ],
        persist: false,
    );

    $array = $user->toArray();

    expect($array[Age::getName()])
        ->toBe($age)
        ->and($array[Balance::getName()])
        ->toBe($balance)
        ->and($array[CreatedAt::getName()])
        ->toBe($createdAt->toISOString())
        ->and($array[Email::getName()])
        ->toBe($email)
        ->and($array[Status::getName()])
        ->toBe($status->value)
        ->and($array[Type::getName()])
        ->toBe($type->value);

});

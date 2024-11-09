<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\Age;
use Mom\Data\Tests\Unit\User\Properties\Balance;
use Mom\Data\Tests\Unit\User\Properties\CreatedAt;
use Mom\Data\Tests\Unit\User\Properties\Email;
use Mom\Data\Tests\Unit\User\User;

test('fake method', function (): void {
    $age = fake()->randomNumber();
    $balance = fake()->randomFloat();
    $createdAt = now()->toDateTimeString();
    $email = fake()->safeEmail();

    $user = User::fake(
        attributes: [
            Age::getDatabaseTableColumnName() => $age,
            Balance::getDatabaseTableColumnName() => $balance,
            CreatedAt::getDatabaseTableColumnName() => $createdAt,
            Email::getDatabaseTableColumnName() => $email,
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
        ->toBe($email);
});

<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\User;

test('toArray method', function (): void {
    $age = fake()->numberBetween(18, 100);
    $balance = fake()->randomFloat(2, 10, 1000);
    $user = User::new()
        ->setAge($age)
        ->setBalance($balance);

    $array = $user->toArray();

    expect($array['age'])
        ->toBe($age)
        ->and($array['balance'])
        ->toBe($balance);
});

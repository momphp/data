<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\Balance;

test('from method', function (): void {
    $balanceOne = Balance::from(18.40);
    $balanceTwo = Balance::from($balanceOne);

    expect($balanceOne->toFloat())
        ->toBe(18.40)
        ->and($balanceTwo->toFloat())
        ->toBe(18.40);
});

test('accepts ints and numeric strings', function (int|string $input, float $expected): void {
    expect(Balance::from($input)->toNullableFloat())->toBe($expected);
})->with([
    [5, 5.0],
    ['12.50', 12.5],
    ['-3', -3.0],
]);

test('rejects non-numeric strings', function (string $input): void {
    expect(Balance::from($input)->toNullableFloat())->toBeNull();
})->with(['abc', '']);

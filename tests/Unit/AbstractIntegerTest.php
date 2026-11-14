<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\Age;

test('from method', function (): void {
    $ageOne = Age::from(18);
    $ageTwo = Age::from($ageOne);

    expect($ageOne->toInteger())
        ->toBe(18)
        ->and($ageTwo->toInteger())
        ->toBe(18);
});

test('accepts integer strings', function (string $input, int $expected): void {
    expect(Age::from($input)->toNullableInteger())->toBe($expected);
})->with([
    ['42', 42],
    ['-7', -7],
    ['0', 0],
]);

test('rejects non-integer strings', function (string $input): void {
    expect(Age::from($input)->toNullableInteger())->toBeNull();
})->with(['4.2', 'abc', '']);

test('toInteger falls back to zero', function (): void {
    expect(Age::new()->toInteger())->toBe(0);
});

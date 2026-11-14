<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\CreatedAt;

test('from method', function (): void {
    $date = now()->subDays(3)->toImmutable();
    $createdOne = CreatedAt::from($date->toDateTimeString());
    $createdTwo = CreatedAt::from($createdOne);
    $createdThree = CreatedAt::from($date);

    expect($createdOne->toDateString())
        ->toBe($date->toDateString())
        ->and($createdTwo->toDateString())
        ->toBe($date->toDateString())
        ->and($createdThree->toDateString())
        ->toBe($date->toDateString());
});

test('accepts mutable dates', function (DateTimeInterface $input): void {
    $created = CreatedAt::from($input);

    expect($created->toNullableCarbon())
        ->toBeInstanceOf(Carbon\CarbonImmutable::class)
        ->and($created->toDateTimeString())
        ->toBe('2026-01-02 03:04:05');
})->with([
    'Carbon' => fn (): Carbon\Carbon => Carbon\Carbon::parse('2026-01-02 03:04:05'),
    'DateTime' => fn (): DateTime => new DateTime('2026-01-02 03:04:05'),
]);

test('empty strings are null instead of now', function (string $input): void {
    expect(CreatedAt::from($input)->toNullableCarbon())->toBeNull();
})->with(['', '   ']);

test('ISO strings match Carbon toISOString', function (string $input): void {
    $created = CreatedAt::from($input);

    expect($created->toNullableISOString())
        ->toBe(Carbon\CarbonImmutable::parse($input)->toISOString())
        ->and($created->toISOString())
        ->toBe(Carbon\CarbonImmutable::parse($input)->toISOString());
})->with([
    '2026-01-02 03:04:05',
    '2026-01-02T03:04:05.123456+08:00',
    '1999-12-31 23:59:59.5 America/New_York',
    '0001-01-01 00:00:00',
    '9999-12-31 23:59:59',
    '+10000-01-01 00:00:00',
]);

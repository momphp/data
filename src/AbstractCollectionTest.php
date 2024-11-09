<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Mom\Data\AbstractCollection;

class Roles extends AbstractCollection
{
    public static function getName(): string
    {
        return 'roles';
    }
}

test('fromArray method', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromArray($array);

    expect($roles->toArray())->toBe($array);
});

test('fromNullableArray method: not null', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromNullableArray($array);

    expect($roles->toArray())->toBe($array);
});

test('fromNullableArray method: null', function (): void {
    $roles = Roles::fromNullableArray(null);

    expect($roles->toArray())->toBeEmpty();
});

test('fromCollection method', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromCollection(collect($array));

    expect($roles->toArray())->toBe($array);
});

test('fromNullableCollection method: not null', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromNullableCollection(collect($array));

    expect($roles->toArray())->toBe($array);
});

test('fromNullableCollection method: null', function (): void {
    $roles = Roles::fromNullableCollection(null);

    expect($roles->toArray())->toBeEmpty();
});

test('toNullableArray method: not null', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromNullableArray($array);

    expect($roles->toNullableArray())->toBe($array);
});

test('toNullableArray method: null', function (): void {
    $roles = Roles::fromNullableArray(null);

    expect($roles->toNullableArray())->toBeNull();
});

test('toNullableCollection method: not null', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromNullableArray($array);

    expect($roles->toNullableCollection())->toBeInstanceOf(Collection::class);
});

test('toNullableCollection method: null', function (): void {
    $roles = Roles::fromNullableArray(null);

    expect($roles->toNullableCollection())->toBeNull();
});

test('toNullablePrimitive method: not null', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromNullableArray($array);

    expect($roles->toPrimitive())->toBeInstanceOf(Collection::class);
});

test('toNullablePrimitive method: null', function (): void {
    $roles = Roles::fromNullableArray(null);

    expect($roles->toPrimitive())->toBeNull();
});

test('toCollection method: not null', function (): void {
    $array = [
        'admin',
        'user',
    ];

    $roles = Roles::fromNullableArray($array);

    expect($roles->toCollection())
        ->toBeInstanceOf(Collection::class)
        ->and($roles->toCollection()->toArray())
        ->toBe($array);
});

test('toCollection method: null', function (): void {
    $roles = Roles::fromNullableArray(null);

    expect($roles->toCollection())
        ->toBeInstanceOf(Collection::class)
        ->and($roles->toCollection()->isEmpty())
        ->toBeTrue();
});

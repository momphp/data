<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\User;

test('toArray method', function (): void {
    $age = fake()->numberBetween(18, 100);
    $balance = fake()->randomFloat(2, 10, 1000);
    $roles = [
        'admin',
        'user',
    ];
    $user = User::new()
        ->setAge($age)
        ->setBalance($balance)
        ->setRoles($roles);

    $array = $user->toArray();

    expect($array['age'])
        ->toBe($age)
        ->and($array['balance'])
        ->toBe($balance)
        ->and($array['roles'])
        ->toBe($roles);
});

test('from with a data object and no method uses fromData', function (): void {
    expect(User::from(User::new()))->toBeInstanceOf(User::class);
});

test('from with an unrecognised value returns an empty instance', function (): void {
    $user = User::from('not-data');

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->getEmail()->isNull())->toBeTrue();
});

test('forResource reports the concrete data class', function (): void {
    expect(User::new()->forResource(new Illuminate\Http\Request)['class'])->toBe(User::class);
});

test('toJson throws on values that cannot be encoded', function (): void {
    User::new()->setEmail("\xB1\x31")->toJson();
})->throws(JsonException::class);

test('toArray reads private properties', function (): void {
    $role = Mom\Data\Tests\Unit\Role\Role::fromArray(['name' => 'admin', 'uuid' => 'abc']);

    expect($role->toArray())->toMatchArray(['name' => 'admin', 'uuid' => 'abc'])
        ->and($role->forDatabaseCreate())->toMatchArray(['name' => 'admin', 'uuid' => 'abc']);
});

test('forResource limits output to requested fields', function (array $fields, array $expectedKeys): void {
    $user = User::fromArray(['email' => 'a@example.com', 'age' => 30, 'roles' => ['admin']]);

    expect(array_keys($user->forResource(new Illuminate\Http\Request(query: ['fields' => $fields]))))
        ->toEqualCanonicalizing($expectedKeys);
})->with([
    'single field' => [['email'], ['email']],
    'with class' => [['email', 'class'], ['email', 'class']],
    'nested selection' => [['roles' => ['x'], 'age'], ['roles', 'age']],
    'prefixed nested selection' => [['user_roles' => ['y']], ['roles']],
    'unknown field' => [['nope'], []],
]);

test('value property metadata is cached per class', function (): void {
    User::new();
    Mom\Data\Tests\Unit\Role\Role::new();

    $cache = (new ReflectionClass(Mom\Data\AbstractData::class))->getStaticPropertyValue('valuePropertyCache');

    expect($cache)->toHaveKeys([User::class, Mom\Data\Tests\Unit\Role\Role::class])
        ->and(array_keys($cache[User::class]))->toBe(['age', 'balance', 'createdAt', 'email', 'roles', 'status', 'type', 'uuid']);
});

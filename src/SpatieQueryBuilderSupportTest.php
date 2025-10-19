<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Mom\Data\Tests\Unit\User\User;
use Spatie\QueryBuilder\AllowedFilter;

it('resolves allowed fields recursively', function (): void {
    $fields = User::allowedFields();

    expect($fields)->toContain('created_at', 'email', 'roles', 'status', 'type', 'uuid')
        ->and($fields)->not()->toContain('age', 'balance');
});

it('resolves allowed includes recursively', function (): void {
    $includes = User::allowedIncludes();

    expect($includes)->toContain('roles');
});

it('resolves allowed sorts recursively', function (): void {
    $sorts = User::allowedSorts();

    expect($sorts)->toContain('age')
        ->and($sorts)->not()->toContain('balance');
});

it('resolves allowed filters recursively', function (): void {
    $filters = User::allowedFilters();

    $names = array_map(
        fn ($filter) => $filter->getName(),
        $filters
    );

    expect($names)->toContain('age')
        ->and($names)->not()->toContain('restricted_field')
        ->and($filters[0])->toBeInstanceOf(AllowedFilter::class);
});

it('applies Gate policy restrictions', function (): void {
    Gate::define('filterField', fn ($user, $modelClass, $field) => ! str_contains($field, 'body'));

    $filters = User::allowedFilters();
    $names = array_map(fn ($f) => $f->getName(), $filters);

    expect($names)->not()->toContain('body');
});

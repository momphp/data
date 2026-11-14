<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mom\Data\Tests\Unit\User\User;
use Mom\Data\Tests\Unit\User\UserEloquentModel;

beforeEach(function (): void {
    Schema::create('user_eloquent_models', function (Blueprint $table): void {
        $table->id();
        $table->integer('age')->nullable();
        $table->decimal('balance', 12, 6)->nullable();
        $table->dateTime('created_at')->nullable();
        $table->string('email')->nullable();
        $table->json('roles')->nullable();
        $table->string('status')->nullable();
        $table->integer('type')->nullable();
        $table->string('uuid')->nullable();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('user_eloquent_models');
});

test('fake persists the model and marks it as existing', function (): void {
    $user = User::fake(['roles' => '["admin"]']);

    expect($user->isExistsInDatabase())->toBeTrue()
        ->and($user->getEloquentModel())->toBeInstanceOf(UserEloquentModel::class)
        ->and($user->getRoles()->toArray())->toBe(['admin'])
        ->and(UserEloquentModel::query()->count())->toBe(1);
});

test('fakeCollection persists models and marks each as existing', function (): void {
    $users = User::fakeCollection(['roles' => '["admin"]'], count: 3);

    expect($users)->toHaveCount(3)
        ->and($users->keys()->all())->toBe([0, 1, 2])
        ->and($users->every(fn (User $user): bool => $user->isExistsInDatabase()))->toBeTrue()
        ->and($users->every(fn (User $user): bool => $user->getEloquentModel() instanceof UserEloquentModel))->toBeTrue()
        ->and(UserEloquentModel::query()->count())->toBe(3);
});

test('fakeCollection without persisting leaves the database empty', function (): void {
    $users = User::fakeCollection(['roles' => '["admin"]'], count: 2, persist: false);

    expect($users)->toHaveCount(2)
        ->and($users->every(fn (User $user): bool => $user->notExistsInDatabase()))->toBeTrue()
        ->and(UserEloquentModel::query()->count())->toBe(0);
});

test('fakeCollection loads the requested relations', function (): void {
    $users = User::fakeCollection(['roles' => '["admin"]'], count: 2, with: ['peers']);

    expect($users->every(fn (User $user): bool => $user->getEloquentModel()->relationLoaded('peers')))->toBeTrue();
});

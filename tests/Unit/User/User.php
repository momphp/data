<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mom\Data\AbstractData;
use Mom\Data\AbstractValue;
use Mom\Data\Tests\Unit\User\Properties\Age;
use Mom\Data\Tests\Unit\User\Properties\Balance;
use Mom\Data\Tests\Unit\User\Properties\CreatedAt;
use Mom\Data\Tests\Unit\User\Properties\Email;
use Mom\Data\Tests\Unit\User\Properties\Roles;
use Mom\Data\Tests\Unit\User\Properties\Uuid;

class User extends AbstractData
{
    public function __construct(
        public Age $age,
        public Balance $balance,
        public CreatedAt $createdAt,
        public Email $email,
        public Roles $roles,
        public Uuid $uuid,
    ) {}

    public static function getFactory(): Factory
    {
        return UserEloquentModel::factory();
    }

    public function getPrimaryKey(): AbstractValue
    {
        return $this->getUuid();
    }

    public function getAge(): Age
    {
        return $this->age;
    }

    public function setAge(mixed $age): User
    {
        $this->age = new Age($age);

        return $this;
    }

    public function getBalance(): Balance
    {
        return $this->balance;
    }

    public function setBalance(mixed $balance): User
    {
        $this->balance = new Balance($balance);

        return $this;
    }

    public function getCreatedAt(): CreatedAt
    {
        return $this->createdAt;
    }

    public function setCreatedAt(mixed $createdAt): User
    {
        $this->createdAt = new CreatedAt($createdAt);

        return $this;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(mixed $email): User
    {
        $this->email = new Email($email);

        return $this;
    }

    public function getRoles(): Roles
    {
        return $this->roles;
    }

    public function setRoles(mixed $roles): User
    {
        $this->roles = new Roles($roles);

        return $this;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function setUuid(mixed $uuid): User
    {
        $this->uuid = new Uuid($uuid);

        return $this;
    }
}

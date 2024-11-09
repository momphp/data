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
        private Age $age,
        private Balance $balance,
        private CreatedAt $createdAt,
        private Email $email,
        private Roles $roles,
        private Uuid $uuid,
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

    public function setAge(Age $age): User
    {
        $this->age = $age;

        return $this;
    }

    public function getBalance(): Balance
    {
        return $this->balance;
    }

    public function setBalance(Balance $balance): User
    {
        $this->balance = $balance;

        return $this;
    }

    public function getCreatedAt(): CreatedAt
    {
        return $this->createdAt;
    }

    public function setCreatedAt(CreatedAt $createdAt): User
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): Roles
    {
        return $this->roles;
    }

    public function setRoles(Roles $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): User
    {
        $this->uuid = $uuid;

        return $this;
    }
}

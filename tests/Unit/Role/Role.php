<?php

declare(strict_types=1);

namespace Mom\Data\Tests\Unit\Role;

use Mom\Data\AbstractData;
use Mom\Data\Tests\Unit\Role\Properties\Name;
use Mom\Data\Tests\Unit\Role\Properties\Uuid;

class Role extends AbstractData
{
    public function __construct(
        private Name $name,
        private Uuid $uuid,
    ) {}

    public function getName(): Name
    {
        return $this->name;
    }

    public function setName(Name $name): Role
    {
        $this->name = $name;

        return $this;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): Role
    {
        $this->uuid = $uuid;

        return $this;
    }
}

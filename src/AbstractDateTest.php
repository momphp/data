<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\CreatedAt;

test('from method', function (): void {
    $date = now()->subDays(3);
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

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

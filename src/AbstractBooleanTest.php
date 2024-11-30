<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\IsAdmin;

test('from method', function (): void {
    $isAdminOne = IsAdmin::from(true);
    $isAdminTwo = IsAdmin::from($isAdminOne);
    $isAdminThree = IsAdmin::from(1);
    $isAdminFour = IsAdmin::from('true');
    $isAdminFive = IsAdmin::from('yes');
    $isAdminSix = IsAdmin::from(0);
    $isAdminSeven = IsAdmin::from('false');
    $isAdminEight = IsAdmin::from('no');

    expect($isAdminOne->toBoolean())
        ->toBeTrue()
        ->and($isAdminTwo->toBoolean())
        ->toBeTrue()
        ->and($isAdminThree->toBoolean())
        ->toBeTrue()
        ->and($isAdminFour->toBoolean())
        ->toBeTrue()
        ->and($isAdminFive->toBoolean())
        ->toBeTrue()
        ->and($isAdminSix->toBoolean())
        ->toBeFalse()
        ->and($isAdminSeven->toBoolean())
        ->toBeFalse()
        ->and($isAdminEight->toBoolean())
        ->toBeFalse();
});

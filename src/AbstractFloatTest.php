<?php

declare(strict_types=1);

use Mom\Data\Tests\Unit\User\Properties\Balance;

test('from method', function (): void {
    $balanceOne = Balance::from(18.40);
    $balanceTwo = Balance::from($balanceOne);

    expect($balanceOne->toFloat())
        ->toBe(18.40)
        ->and($balanceTwo->toFloat())
        ->toBe(18.40);
});

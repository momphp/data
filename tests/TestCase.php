<?php

declare(strict_types=1);

namespace Mom\Data\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
    }
}

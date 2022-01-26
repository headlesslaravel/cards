<?php

namespace HeadlessLaravel\Cards\Tests;

use HeadlessLaravel\Cards\CardsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            CardsServiceProvider::class,
        ];
    }
}

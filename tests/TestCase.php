<?php

declare(strict_types=1);

namespace _vendor_name_\_vendor_package_\Tests;

use _vendor_name_\_vendor_package_\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }
}

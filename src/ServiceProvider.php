<?php

declare(strict_types=1);

namespace _vendor_name_\_vendor_package_;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/_package_name_.php' => config_path('.php'),
        ]);
    }
}

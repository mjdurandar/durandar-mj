<?php

namespace App\Providers;

use App\DTOs\OpeningHoursDTO;
use App\Interfaces\OpeningHoursInterface;
use App\Services\OpeningHoursService;
use Illuminate\Support\ServiceProvider;

class OpeningHoursServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpeningHoursInterface::class, function ($app) {
            $config = OpeningHoursDTO::fromArray(config('opening-hours'));
            return new OpeningHoursService($config);
        });
    }
} 
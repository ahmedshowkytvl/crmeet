<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DateParserService;
use App\Services\EmployeeDataService;
use App\Services\ExcelProcessorService;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DateParserService::class, function ($app) {
            return new DateParserService();
        });

        $this->app->singleton(EmployeeDataService::class, function ($app) {
            return new EmployeeDataService($app->make(DateParserService::class));
        });

        $this->app->singleton(ExcelProcessorService::class, function ($app) {
            return new ExcelProcessorService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

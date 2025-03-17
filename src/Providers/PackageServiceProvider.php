<?php

namespace ExpertShipping\Spl\Providers;

use ExpertShipping\Spl\Helpers\Money;
use ExpertShipping\Spl\Services\InsuranceService;
use ExpertShipping\Spl\Services\OpenAIService;
use ExpertShipping\Spl\Services\SearchSelectService;
use Illuminate\Support\ServiceProvider;

final class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'spl');
    }

    public function register()
    {
        $this->app->bind('SearchSelect', function () {
            return new SearchSelectService();
        });

        $this->app->bind('SPL.money', function () {
            return new Money();
        });

        $this->loadViewsFrom(__DIR__.'/../Views', 'spl');

        $this->mergeConfigFrom(__DIR__.'/../Config/spl.php', 'spl');

        $this->app->singleton('insurance', function () {
            return new InsuranceService();
        });

        $this->app->bind('openai-text', function ($app) {
            return new OpenAIService();
        });

        $this->publishes([
            __DIR__ . '/../Config/openai.php' => config_path('openai.php'),
        ], 'config');
    }
}

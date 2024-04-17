<?php

namespace ExpertShipping\Spl\Providers;

use ExpertShipping\Spl\Facades\SearchSelectFacade;
use ExpertShipping\Spl\Services\SearchSelectService;
use Illuminate\Support\ServiceProvider;

final class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }

    public function register()
    {
        $this->app->bind('SearchSelect', function () {
            return new SearchSelectService();
        });

        $this->app->bind('SearchSelect', SearchSelectFacade::class);

        $this->loadViewsFrom(__DIR__.'/../Views', 'spl');

        $this->mergeConfigFrom(__DIR__.'/../Config/spl.php', 'spl');
    }
}

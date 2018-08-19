<?php

namespace App\Providers;

use App\Spotify\Client\ServiceConnection;
use App\Spotify\Contracts\ApiConnection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ApiConnection::class, function () {
            return new ServiceConnection;
        });
    }
}

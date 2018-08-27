<?php

namespace App\Providers;

use App\GoogleMusicFinder;
use App\Spotify\Client\ServiceConnection;
use App\Spotify\Contracts\ApiConnection;
use App\SpotifyFinder;
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

        $this->app->bind(GoogleMusicFinder::class, function () {
            return new GoogleMusicFinder('googleMusic');
        });

        $this->app->bind(SpotifyFinder::class, function ($app) {
            return new SpotifyFinder('spotify', $app->get(ApiConnection::class));
        });
    }
}

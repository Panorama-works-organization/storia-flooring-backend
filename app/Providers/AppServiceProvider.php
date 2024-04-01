<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shopify\Auth\FileSessionStorage;
use Shopify\Context;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        Schema::defaultStringLength(191);

        $this->app->bind(
            'Illuminate\Contracts\Hashing\Hasher',
            'Illuminate\Hashing\BcryptHasher'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Context::initialize(
            env('SHOPIFY_API_KEY'),
            env('SHOPIFY_API_SECRET'),
            explode(',', env('SHOPIFY_APP_SCOPES')),
            env('SHOPIFY_APP_HOST_NAME'),
            new FileSessionStorage(asset('logs')),
            //'unstable'
        );
    }
}

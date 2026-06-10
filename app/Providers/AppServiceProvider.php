<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\OpenAI\Contracts\ClientContract::class, fn () =>
            \OpenAI\OpenAI::factory()
                ->withApiKey((string) config('openai.api_key'))
                ->withBaseUri((string) config('openai.base_uri'))
                ->make()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

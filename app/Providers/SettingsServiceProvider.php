<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Factory;
use App\Models\Settings;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Factory $cache, Settings $settings): void
    {
        $settings = $cache->remember('settings', 60, function() use ($settings)
        {
            return $settings->pluck('value', 'id')->all();
        });

        config()->set('settings', $settings);
    }
}

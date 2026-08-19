<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
// use App\Models\Settings;

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
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return; // Skip during artisan commands like migrate
        }

        try {
            $settings = Cache::rememberForever('app.settings', function () {
                return DB::table('settings')->pluck('value', 'id')->toArray();
            });

            // Resolved outside of the cache, the url depends on the host being
            // served. Companies override it through the ResolveCompany middleware.
            $settings['logo_url'] = asset('storage/assets/' . ($settings['logo_main'] ?? ''));

            config()->set('settings', $settings);
        } catch (\Throwable $e) {
            // You can log this in development
            // logger()->error('Settings load failed: ' . $e->getMessage());
        }
    }
}

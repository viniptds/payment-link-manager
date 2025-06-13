<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
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
        if ($this->app->runningInConsole()) {
            return; // Skip during artisan commands like migrate
        }

        // Check DB connection and table existence
            if (Schema::hasTable('settings')) {
                $settings = $cache->remember('settings', 60, function () use ($settings) {
                // TODO: check if this works when no setting is placed
                $return = $settings->pluck('value', 'id')->all();

                if (empty($return)) {
                    $return = $settings->getFactoryValues();
                }
                return $return;
            });

            config()->set('settings', $settings);
        }
    }
}

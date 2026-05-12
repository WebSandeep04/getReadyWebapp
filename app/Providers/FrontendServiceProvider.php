<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\FrontendSetting;

class FrontendServiceProvider extends ServiceProvider
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
        // Share frontend settings with all views
        View::composer('*', function ($view) {
            $frontendSettings = FrontendSetting::getAllAsArray();
            $view->with('frontendSettings', $frontendSettings);
        });

        // Create a global helper function
        if (!function_exists('frontend_setting')) {
            function frontend_setting($key, $default = null) {
                return FrontendSetting::getValue($key, $default);
            }
        }
    }
}

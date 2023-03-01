<?php

namespace Cornatul\Marketing\Base;

use Cornatul\Marketing\Base\Providers\MarketingPortalAppServiceProvider;
use Cornatul\Marketing\Base\Services\Marketingportal;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Cornatul\Marketing\Base\Console\Commands\CampaignDispatchCommand;
use Cornatul\Marketing\Base\Providers\EventServiceProvider;
use Cornatul\Marketing\Base\Providers\FormServiceProvider;
use Cornatul\Marketing\Base\Providers\ResolverProvider;
use Cornatul\Marketing\Base\Providers\RouteServiceProvider;
use Cornatul\Marketing\Base\Services\Sendportal;

class MarketingPortalBaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot():void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('marketing.php'),
            ], 'marketing-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/marketing'),
            ], 'marketing-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/marketing'),
            ], 'marketing-lang');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/marketing'),
            ], 'marketing-assets');

            $this->commands([
                CampaignDispatchCommand::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command(CampaignDispatchCommand::class)->everyMinute()->withoutOverlapping();
            });
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'marketing');
        $this->loadJsonTranslationsFrom(resource_path('lang/vendor/marketing'));
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'marketing');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the application services.
     */
    public function register():void
    {
        // Providers.
        $this->app->register(MarketingPortalAppServiceProvider::class);

        $this->app->register(EventServiceProvider::class);

        $this->app->register(FormServiceProvider::class);

        $this->app->register(RouteServiceProvider::class);

        $this->app->register(ResolverProvider::class);

        // Facade.
        $this->app->bind('marketing', static function (Application $app) {
            return $app->make(Marketingportal::class);
        });

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'marketing');
    }
}

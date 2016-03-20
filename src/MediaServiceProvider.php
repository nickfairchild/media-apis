<?php
namespace Media;

use Illuminate\Support\ServiceProvider;
use Media\TVDB\TVDB;

class MediaServiceProvider extends ServiceProvider
{
    
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/media.php' => config_path('media.php')
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTvdb();

        $this->app->alias('tvdb', 'Media\TVDB');
    }

    /**
     * Register the TVDB instance.
     *
     * @return void
     */
    private function registerTvdb()
    {
        $this->app->singleton('tvdb', function($app) {
            return new TVDB($app['config']['tvdb.host'], $app['config']['tvdb.key']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['tvdb', 'Media\TVDB'];
    }
}
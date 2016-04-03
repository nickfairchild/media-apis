<?php
namespace Nick\Media;

use Illuminate\Support\ServiceProvider;
use Nick\Media\Fanart\Fanart;
use Nick\Media\TVDB\TVDB;

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
        $this->registerFanart();

        $this->app->alias('tvdb', 'Nick\Media\TVDB\TVDB');
        $this->app->alias('fanart', 'Nick\Media\Fanart\Fanart');
    }

    /**
     * Register the TVDB instance.
     *
     * @return void
     */
    private function registerTvdb()
    {
        $this->app->singleton('tvdb', function($app) {
            $host = config('media.tvdb.host');
            $api = config('media.tvdb.key');
            return new TVDB($host, $api);
        });
    }

    /**
     * Register the Fanart instance.
     *
     * @return void
     */
    private function registerFanart()
    {
        $this->app->singleton('fanart', function($app) {
            $host = config('media.fanart.host');
            $api = config('media.fanart.key');
            return new Fanart($api);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['tvdb', 'fanart', 'Nick\Media\TVDB\TVDB', 'Nick\Media\Fanart\Fanart'];
    }
}
<?php
namespace  Zwei\LaravelPkgApi\Providers;

use Illuminate\Support\ServiceProvider;
use Zwei\LaravelPkgApi\Exception\ExceptionHandler;

class LaravelApiPkgProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, ExceptionHandler::class);
    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        
    }
}

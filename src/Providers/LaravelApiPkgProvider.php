<?php
namespace  Zwei\LaravelPkgApi\Providers;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class LaravelApiPkgProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $file = dirname(__DIR__).'/../routes/api.v1.php';
        $this->loadRoutesFrom($file);
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

<?php
namespace Zwei\LaravelPkgApi\Tests;

use Zwei\LaravelPkgApi\Providers\LaravelApiPkgProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app); // TODO: Change the autogenerated stub
        $app['config']->set('database.default', env('DB_CONNECTION'));
        $app['config']->set('database.connections.'.env('DB_CONNECTION'), [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);
    
        $app['config']->set('database.connections.'.env('DB_BUSINESS_CONNECTION'), [
            'driver' => 'mysql',
            'host' => env('DB_BUSINESS_HOST'),
            'port' => env('DB_BUSINESS_PORT'),
            'database' => env('DB_BUSINESS_DATABASE'),
            'username' => env('DB_BUSINESS_USERNAME'),
            'password' => env('DB_BUSINESS_PASSWORD'),
        ]);
    }
    
    protected function getPackageProviders($app)
    {
        return [
            LaravelApiPkgProvider::class,
        ];
    }
}

<?php

namespace AbstractEverything\Cart;

use AbstractEverything\Cart\CartManager;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('cart', function($app) {
            return new \AbstractEverything\Cart\CartManager(
                $app['session'],
                $app['config']
            );
        });

        $this->registerListeners();
    }

    public function boot()
    {
        $this->publishConfig();
    }

    /**
     * Publish the configuration file
     * @return null
     */
    protected function publishConfig()
    {
        $this->publishes([
            realpath(__DIR__ . '/..').'/config/cart.php' => config_path('cart.php')
        ], 'config');
    }

    /**
     * Register listeners for this service
     * @return null
     */
    protected function registerListeners()
    {
        $this->app['events']->listen(Logout::class, function() {
            $this->app->make(CartManager::class)->clear();
        });
    }
}
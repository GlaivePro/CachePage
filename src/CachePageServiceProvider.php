<?php

namespace GlaivePro\CachePage;

use Illuminate\Support\ServiceProvider;

class CachePageServiceProvider extends ServiceProvider
{
	/**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
		$packageConfigFile = __DIR__.'/config/config.php';
		
		$this->publishes([
				$packageConfigFile => config_path('cachepage.php'),
		], 'config');
				
		$this->mergeConfigFrom($packageConfigFile, 'cachepage');
		
		$router->middleware('gpcachepage', \GlaivePro\CachePage\Middleware\CachePage::class);
    }
   
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
		//
    }
}
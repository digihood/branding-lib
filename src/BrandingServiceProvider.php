<?php

namespace Digihood\Branding;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
class BrandingServiceProvider extends ServiceProvider {



    protected $commands = [
        \Digihood\Branding\App\Console\Commands\installBranding::class,
    ];
    public $routeFilePath = __DIR__.'/routes/web.php';
    
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->setupCustomRoutes($this->app->router);
            // use the vendor configuration file as fallback
       
    }
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
       

    }

   
    /**
     * Load custom routes file.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupCustomRoutes(Router $router)
    {
    
        // if the custom routes file is published, register its routes
        if (file_exists($this->routeFilePath)) {
            $this->loadRoutesFrom($this->routeFilePath);
        }
    }


}

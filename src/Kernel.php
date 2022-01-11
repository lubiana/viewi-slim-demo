<?php

namespace App;

use App\Adapters\ViewiSymfonyAdapter;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Viewi\Routing\Route;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private ViewiSymfonyAdapter $viewiAdapter;

    public function boot()
    {
        $this->viewiAdapter = new ViewiSymfonyAdapter();
        Route::setAdapter($this->viewiAdapter);
        include __DIR__ . '/ViewiApp/viewi.php';
        parent::boot();
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $extensions = '{php,yaml}';

        $routes->import('../config/{routes}/' . $this->environment . "/*.$extensions");
        $routes->import("../config/{routes}/*.$extensions");
        $routes->import("../config/{routes}.$extensions");
        
        $this->viewiAdapter->registerRoutes($routes);
    }
}

<?php

namespace App\Adapters;

use App\Kernel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Viewi\Routing\RouteAdapterBase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Viewi\Routing\Route;
use Viewi\Routing\RouteItem;

class ViewiSymfonyAdapter extends RouteAdapterBase
{
    private int $index = 0; // unique names

    public function register($method, $url, $component, $defaults)
    {
        // skip
    }

    public function handle($method, $url, $params = null)
    {
        $kernel = new Kernel('dev', false);
        $request = Request::create($url, $method, $params ?? []);
        $response = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
        if($response instanceof RawJsonResponse)
        {
            return $response->getRawData();
        }
        return json_decode($response->getContent());
    }

    public function registerRoutes(RoutingConfigurator $routes)
    {
        $viewiRoutes = Route::getRoutes();
        /** @var RouteItem $route */
        foreach ($viewiRoutes as $viewiRoute) {
            $route = $routes->add($viewiRoute->component . (++$this->index), $viewiRoute->url)
                ->controller(ViewiSymfonyComponent::class)
                ->methods([$viewiRoute->method]);
            $defaults  = ['__viewi_component' => $viewiRoute->component] + ($viewiRoute->defaults ?? []);
            $route->defaults($defaults);
        }
    }
}

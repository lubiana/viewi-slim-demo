<?php

namespace App\Adapters;

use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Viewi\Routing\RouteAdapterBase;
use Viewi\Routing\Route;

class ViewiSlimAdapter extends RouteAdapterBase
{
    private int $index = 0; // unique names
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function register($method, $url, $component, $defaults)
    {
        // skip
    }

    public function handle($method, $url, $params = null)
    {
        $request = (new ServerRequestFactory())->createServerRequest($method, $url, $params ?? []);
        $response = $this->app->handle($request);
        if($response instanceof RawJsonResponse)
        {
            return $response->getRawData();
        }
        return json_decode($response->getContent());
    }

    public function registerRoutes()
    {
        $viewiRoutes = Route::getRoutes();

        /** @var Route $route */
        foreach ($viewiRoutes as $route) {
            $this->app->any($route->url, new ViewiSlimComponent($route->component));
        }
    }
}

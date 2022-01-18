<?php

declare(strict_types=1);

namespace App\Adapters;

use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Viewi\Routing\Route;
use Viewi\Routing\RouteAdapterBase;

final class ViewiSlimAdapter extends RouteAdapterBase
{
    private int $index = 0; // unique names
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function register($method, $url, $component, $defaults): void
    {
        // skip
    }

    public function handle($method, $url, $params = null)
    {
        $method = strtoupper($method);
        $request = (new ServerRequestFactory())->createServerRequest($method, $url, $params ?? []);
        $response = $this->app->handle($request);
        if ($response instanceof RawJsonResponse) {
            return $response->getRawData();
        }
        return json_decode($response->getContent());
    }

    public function registerRoutes(): void
    {
        $viewiRoutes = Route::getRoutes();

        /** @var Route $route */
        foreach ($viewiRoutes as $route) {
            $method = $route->method;
            $this->app->$method($route->url, new ViewiSlimComponent($route->component));
        }
    }
}

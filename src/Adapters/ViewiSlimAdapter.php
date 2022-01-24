<?php

declare(strict_types=1);

namespace App\Adapters;

use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Viewi\Routing\Route;
use Viewi\Routing\RouteAdapterBase;

final class ViewiSlimAdapter extends RouteAdapterBase
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function register($method, $url, $component, $defaults): void
    {
        $this->app->$method($url, new ViewiSlimComponent($component));
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
}

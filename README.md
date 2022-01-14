# Viewi ft. Symfony

## Install Symfony (API)

    composer require slim/slim:"4.*"
    composer require slim/psr7

## Install Viewi

    composer require viewi/viewi
    vendor/bin/viewi new -e

## Change `public/index.php`

Remove Viewi related stuff

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

/** @var \Slim\App $app */
$app = (require __DIR__ . '/../src/SlimViewi.php')();

$app->run();
```

## Configure SlimApp `src/SlimViewi.php`

here we define our mocked api controller and register the slim viewi adapter

```php
<?php
use App\Adapters\RawJsonResponse;
use App\Adapters\ViewiSlimAdapter;
use Components\Models\PostModel;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Headers;
use Viewi\Routing\Route;

return function () : App {
    $app = AppFactory::create();

    $app->get('/api', function (Request $request, Response $response): RawJsonResponse {
        $body = $response->getBody();
        $postModel = new PostModel();
        $postModel->Name = 'Symfony ft. Viewi';
        $postModel->Version = 1;
        $headers = new Headers($response->getHeaders());
        $response = new RawJsonResponse($response->getStatusCode(), $headers, $response->getBody());
        $response->setData($postModel);
        $body->write(json_encode($postModel));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    });

    require __DIR__ . '/../src/ViewiApp/viewi.php';
    $adapter = new ViewiSlimAdapter($app);
    Route::setAdapter($adapter);
    $adapter->registerRoutes();

    return $app;
};
```

## Configure Slim

Create Viewi adapter for Symfony

`src\Adapters\ViewiSlimComponent.php`

```php
<?php

namespace App\Adapters;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Viewi\App;
use Viewi\WebComponents\Response as ViewiResponse;

class ViewiSlimComponent
{
    private string $component;

    public function __construct(string $component) {
        $this->component = $component;
    }
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $params['__viewi_component'] = $this->component;

        $vResponse = App::run($this->component, $params);
        if (is_string($vResponse)) { // html
            $body = $response->getBody();
            $body->write($vResponse);
            return  $response
                ->withBody($body);
        }
        if ($vResponse instanceof ViewiResponse) {
            /** @var ViewiResponse $response */
            if ($vResponse->Stringify) { // ViewiResponse with the object (should never happen, Symfony handles the API)
                return new JsonResponse(
                    $response->Content,
                    $response->StatusCode,
                    $response->Headers
                );
            }

            return new Response(
                $response->Content,
                $response->StatusCode,
                $response->Headers
            );
        }
        // json (should never happen, Symfony handles the API
        $body = $response->getBody();
        $body->write($vResponse);
        return  $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
```

`src\Adapters\ViewiSymfonyAdapter.php`

```php
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
        $method = strtoupper($method);
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
```

### We need an original data without modifications (not encoded to the json)

`src\Adapters\RawJsonResponse.php`

```php
<?php

namespace App\Adapters;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Response;

class RawJsonResponse extends Response
{
    private $rawData = null;

    public function __construct(
        int $status = StatusCodeInterface::STATUS_OK,
        ?HeadersInterface $headers = null,
        ?StreamInterface $body = null
    ) {
        if (!$headers) {
            $headers = new Headers([], []);
        }
        $headers = $headers->addHeader('Content-Type', 'application/json');
        parent::__construct($status, $headers, $body);
    }

    public function setData($data = [])
    {
        $this->rawData = $data;
    }

    public function getRawData()
    {
        return $this->rawData;
    }
}
```

# API usage

It is recommended to use `RawJsonResponse` instead of `JsonResponse` in your API controllers if you wish to preserve type declarations (type-hinting) inside of Viewi components, for example:

```php
// without RawJsonResponse you will get a type error
$http->get('/api')->then(function (PostModel $data) {
        $this->post = $data;
    }, function ($error) {
        echo $error;
    });
```

# Viewi config

This file is being included a couple of times. So you have to remove declaring constants here or check if they are not declared already.

`src\ViewiApp\config.php`

```php
<?php

use Viewi\PageEngine;

return [
    PageEngine::SOURCE_DIR =>  __DIR__ . '/Components',
    PageEngine::SERVER_BUILD_DIR =>  __DIR__ . '/build',
    PageEngine::PUBLIC_ROOT_DIR => __DIR__ . '/../../public/',
    PageEngine::DEV_MODE => true,
    PageEngine::RETURN_OUTPUT => true,
    PageEngine::COMBINE_JS => true
];
```

___

### If you have any questions or suggestion on how to improve this please reach out to me :)

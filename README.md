# Viewi ft. Symfony

## Install Slim and the PSR7 implementation (API)

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

Here we register the routes and set our Adapters for die Viewi Application

```php
<?php

use App\Action\ApiAction;
use App\Adapters\ViewiSlimAdapter;
use Slim\App;
use Slim\Factory\AppFactory;
use Viewi\Routing\Route;

return function () : App {
    $app = AppFactory::create();

    $app->get('/api', ApiAction::class);

    require __DIR__ . '/../src/ViewiApp/viewi.php';
    $adapter = new ViewiSlimAdapter($app);
    Route::setAdapter($adapter);
    $adapter->registerRoutes();

    return $app;
};
```

## Configure Slim

Create Viewi adapter for Slim

This Adapter is our Requesthandler for HTTP-Request return a prerendered response from Viewi.

If called our slim application dispatches the request and it params to the Viewi application, captures the html output,
adds it to the slim PSR-Response-Object and gives it back to the slim framework to be emitted to the browser.

If for any reason the output from viewi is not a string we simply return the given response-object.

Question: what are other possible outputs by Viwie\App->run() that could happen and why?

`src\Adapters\ViewiSlimComponent.php`

```php
<?php

namespace App\Adapters;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Viewi\App;

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

        return $response;
    }
}
```

This Adapter Connects Viewi with Slim as well as the other way around.

I am not completely clear on the handle() Method, but to my understanding it gets called, if we have api-calls inside of
a server side rendered component.
In this case we create a PSR-Serverrequest and manually dispatch it to slims routehandler. Then we collect the Raw-Data
from the ResponseObject (if available) and return it to Viewis Component Renderer to be used in prerendering of the
component with the correct data from the faked request.

The registerRoutes method simply collects the Routes from our Viewi-Application and registers them in slim.
We collect the Component-Name from the route, pass it to a new ViewiSlimComponent-Object and register that object
as requesthandler.
This way we let Viewi render the correct page whenever a http-request happens directly to a Viewi page. 

`src\Adapters\ViewiSlimAdapter.php`

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
            $method = $route->method;
            $this->app->$method($route->url, new ViewiSlimComponent($route->component));
        }
    }
}
```

### We need an original data without modifications (not encoded to the json)

With this Adapter we extend Slims PSR7 implementation, we need this way to be able to store normal PHP-Objects in the
Response, so we can read and apply them to our components in server side rendered responses.
I added a small static factory that we can call in our action handlers to transform a normal Response-Object into our
RawJsonResponse.
I also modified the setData() Method to automatically modify the response body whenever we add data to our Object:wq

`src\Adapters\RawJsonResponse.php`

```php
<?php

namespace App\Adapters;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
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
        $this->body->write(json_encode($data));
        return $this;
    }


    public function getRawData()
    {
        return $this->rawData;
    }

    public static function fromPsrResponse(ResponseInterface $response) : self
    {
        $headers = new Headers($response->getHeaders());
        return new self($response->getStatusCode(), $headers, $response->getBody());
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

# Viewi ft. Symfony

## Install Symfony (API)

`symfony new my_project_directory`

OR

`composer create-project symfony/skeleton my_project_directory`

## Install Viewi

`composer require viewi/viewi`

`vendor/bin/viewi new -e`

## Change `public/index.php`

Remove Viewi related stuff

```php
<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
```

## Configure Symfony

Create Viewi adapter for Symfony

`src\Adapters\ViewiSymfonyComponent.php`

```php
<?php

namespace App\Adapters;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Viewi\App;
use Viewi\WebComponents\Response as ViewiResponse;

class ViewiSymfonyComponent
{
    public function __invoke(Request $request): Response
    {
        $params = $request->attributes->all();
        $component = $params['__viewi_component'];

        $response = App::run($component, $params);
        if (is_string($response)) { // html
            return new Response(
                $response
            );
        } else if ($response instanceof ViewiResponse) {
            /** @var ViewiResponse $response */
            if ($response->Stringify) { // ViewiResponse with the object (should never happen, Symfony handles the API)
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
        } else { // json (should never happen, Symfony handles the API)
            return new JsonResponse($response);
        }
    }
}
```

`src\Adapters\ViewiSymfonyAdapter.php`

```php
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
        // !!do not use Kernel dev in production!!
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
                ->controller(ViewiSlimComponent::class)
                ->methods([$viewiRoute->method]);
            $defaults  = ['__viewi_component' => $viewiRoute->component] + ($viewiRoute->defaults ?? []);
            $route->defaults($defaults);
        }
    }
}
```

### We need an original data without modifications (not encoded to the json)

`src\Adapters\RawJsonResponse.php`

```php
<?php

namespace App\Adapters;

use Symfony\Component\HttpFoundation\JsonResponse;

class RawJsonResponse extends JsonResponse
{
    private $rawData = null;
    /**
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     * @param bool  $json    If the data is already a JSON string
     */
    public function __construct($data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        $this->rawData = $data;
        parent::__construct($data, $status, $headers, $json);
    }

    public function setData($data = [])
    {
        $this->rawData = $data;
        parent::setData($data);
    }

    public function getRawData()
    {
        return $this->rawData;
    }
}
```

# Register adapter and routes

`src\Kernel.php`

```php
<?php

namespace App;

use App\Adapters\ViewiSlimAdapter;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Viewi\Routing\Route;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private ViewiSlimAdapter $viewiAdapter;

    public function boot()
    {
        // set up Symfony adapter for Viewi 
        $this->viewiAdapter = new ViewiSlimAdapter();
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
        // register routes from Viewi
        $this->viewiAdapter->registerRoutes($routes);
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

<?php

use App\Action\ApiAction;
use App\Adapters\RawJsonResponse;
use App\Adapters\ViewiSlimAdapter;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface;
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
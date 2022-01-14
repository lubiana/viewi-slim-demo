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
<?php

declare(strict_types=1);

use App\Action\ApiAction;
use App\Adapters\ViewiSlimAdapter;
use Slim\Factory\AppFactory;
use Viewi\Routing\Route;

$app = AppFactory::create();

$app->get('/api/posts/{id}', ApiAction::class);

require __DIR__ . '/../src/ViewiApp/viewi.php';
$adapter = new ViewiSlimAdapter($app);
Route::setAdapter($adapter);
$adapter->registerRoutes();

return $app;

<?php

declare(strict_types=1);

use App\Action\ApiAction;
use App\Adapters\RawJsonResponseFactory;
use App\Adapters\ViewiSlimAdapter;
use Slim\Factory\AppFactory;
use Viewi\Routing\Route;

$app = AppFactory::create(
    new RawJsonResponseFactory()
);

$app->get('/api/posts/{id}', ApiAction::class);

$adapter = new ViewiSlimAdapter($app);
Route::setAdapter($adapter);
require __DIR__ . '/../src/ViewiApp/viewi.php';

return $app;

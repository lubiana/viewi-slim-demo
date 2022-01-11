<?php

use App\Controller\ApiController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('api', '/api')->controller([ApiController::class, 'data']);
};

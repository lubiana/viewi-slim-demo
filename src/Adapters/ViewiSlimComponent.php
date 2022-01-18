<?php

declare(strict_types=1);

namespace App\Adapters;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Viewi\App;
use function is_string;

final class ViewiSlimComponent
{
    private string $component;

    public function __construct(string $component)
    {
        $this->component = $component;
    }
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $vResponse = App::run($this->component, $args);
        if (is_string($vResponse)) { // html
            $body = $response->getBody();
            $body->write($vResponse);
            return  $response
                ->withBody($body);
        }

        return $response;
    }
}

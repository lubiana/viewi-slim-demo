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

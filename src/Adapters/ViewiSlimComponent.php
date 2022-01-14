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

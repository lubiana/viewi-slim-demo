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

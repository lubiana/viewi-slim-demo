<?php
declare(strict_types=1);

namespace App\Action;

use App\Adapters\RawJsonResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiAction
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->handle($request, RawJsonResponse::fromPsrResponse($response));
    }

    public abstract function handle(RequestInterface $request, RawJsonResponse $response) : RawJsonResponse;
}
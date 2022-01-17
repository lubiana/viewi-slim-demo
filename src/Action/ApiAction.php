<?php
declare(strict_types=1);

namespace App\Action;

use App\Adapters\RawJsonResponse;
use Components\Models\PostModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ApiAction
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): RawJsonResponse
    {
        $response = RawJsonResponse::fromPsrResponse($response);
        $postModel = new PostModel();
        $postModel->Name = 'Slim ft. Viewi';
        $postModel->Version = 1;
        return $response->setData($postModel);
    }
}
<?php
declare(strict_types=1);

namespace App\Action;

use App\Adapters\RawJsonResponse;
use Components\Models\PostModel;
use Psr\Http\Message\RequestInterface;

final class ApiAction
{
    public function __invoke(RequestInterface $request, RawJsonResponse $response): RawJsonResponse
    {
        $response = RawJsonResponse::fromPsrResponse($response);
        $postModel = new PostModel();
        $postModel->Name = 'Symfony ft. Viewi';
        $postModel->Version = 1;
        return $response->setData($postModel);
    }
}
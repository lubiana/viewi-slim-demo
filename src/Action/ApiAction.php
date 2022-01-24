<?php

declare(strict_types=1);

namespace App\Action;

use App\Adapters\RawJsonResponse;
use Components\Models\PostModel;
use Psr\Http\Message\RequestInterface;

final class ApiAction
{
    public function __invoke(RequestInterface $request, RawJsonResponse $response, $args): RawJsonResponse
    {
        $postModel = new PostModel();
        $postModel->Id = (int)$args['id'];
        $postModel->Name = 'Slim ft. Viewi';
        $postModel->Version = 1;
        return $response
            ->setData($postModel)
            ->withJsonHeader();
    }
}

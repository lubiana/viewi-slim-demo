<?php

namespace App\Controller;

use App\Adapters\RawJsonResponse;
use Components\Models\PostModel;
use Symfony\Component\HttpFoundation\Response;

class ApiController
{
    public function data(): Response
    {
        $postModel = new PostModel();
        $postModel->Name = 'Symfony ft. Viewi';
        $postModel->Version = 1;
        

        return new RawJsonResponse($postModel);
    }
}

<?php

namespace App\Adapters;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Response;

class RawJsonResponse extends Response
{
    private $rawData = null;

    public function __construct(
        int $status = StatusCodeInterface::STATUS_OK,
        ?HeadersInterface $headers = null,
        ?StreamInterface $body = null
    ) {
        if (!$headers) {
            $headers = new Headers([], []);
        }
        $headers = $headers->addHeader('Content-Type', 'application/json');
        parent::__construct($status, $headers, $body);
    }

    public function setData($data = [])
    {
        $this->rawData = $data;
    }

    public function getRawData()
    {
        return $this->rawData;
    }
}
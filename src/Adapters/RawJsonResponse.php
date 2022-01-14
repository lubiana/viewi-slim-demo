<?php

namespace App\Adapters;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
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
        $this->body->write(json_encode($data));
        return $this;
    }


    public function getRawData()
    {
        return $this->rawData;
    }

    public static function fromPsrResponse(ResponseInterface $response) : self
    {
        $headers = new Headers($response->getHeaders());
        return new self($response->getStatusCode(), $headers, $response->getBody());
    }
}
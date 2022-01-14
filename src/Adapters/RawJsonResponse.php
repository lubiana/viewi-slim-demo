<?php

namespace App\Adapters;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Response;

class RawJsonResponse extends Response
{
    private $rawData = null;

    public function __construct(int $status = StatusCodeInterface::STATUS_OK, ?HeadersInterface $headers = null, ?StreamInterface $body = null)
    {
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
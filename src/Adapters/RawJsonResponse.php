<?php

declare(strict_types=1);

namespace App\Adapters;

use Slim\Psr7\Response;

final class RawJsonResponse extends Response
{
    private $rawData = null;

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data = [])
    {
        $this->rawData = $data;
        $this->body->write(json_encode($data));
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    public function withJsonHeader(): self
    {
        return $this->withAddedHeader('Content-Type', 'application/json');
    }
}

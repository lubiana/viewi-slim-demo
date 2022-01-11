<?php

namespace App\Adapters;

use Symfony\Component\HttpFoundation\JsonResponse;

class RawJsonResponse extends JsonResponse
{
    private $rawData = null;
    /**
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     * @param bool  $json    If the data is already a JSON string
     */
    public function __construct($data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        $this->rawData = $data;
        parent::__construct($data, $status, $headers, $json);
    }

    public function setData($data = [])
    {
        $this->rawData = $data;
        parent::setData($data);
    }

    public function getRawData()
    {
        return $this->rawData;
    }
}
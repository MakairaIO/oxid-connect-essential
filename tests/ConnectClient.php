<?php

namespace Makaira\OxidConnectEssential\Test;

use JsonException;
use Makaira\HttpClient;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

class ConnectClient
{
    private HttpClient $aggregate;

    private string $connectUrl;

    public function __construct(HttpClient $aggregate, string $connectUrl)
    {
        $this->aggregate  = $aggregate;
        $this->connectUrl = $connectUrl;
    }

    public function request(array $body, array $headers = [])
    {
        $jsonBody = json_encode($body, JSON_THROW_ON_ERROR);

        $response = $this->aggregate->request('POST', $this->connectUrl, $jsonBody, $headers);
        try {
            $response->body = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
        }

        return $response;
    }
}

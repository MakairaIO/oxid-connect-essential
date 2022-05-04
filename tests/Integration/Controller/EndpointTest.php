<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Controller;

use Makaira\OxidConnectEssential\Controller\Endpoint;
use Makaira\OxidConnectEssential\Repository;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Request;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class EndpointTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::setModuleSetting('makaira_connect_secret', parent::SECRET);
    }

    public function testResponsesWith403ForInvalidSignature()
    {
        $request  = $this->getConnectRequest(['action' => 'listLanguages'], 's3cr3t');
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testResponsesWith401IfHeadersAreMissing()
    {
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest(new Request());

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testResponsesWith400IfBodyIsNotJson()
    {
        $request  = $this->getConnectRequest(
            '<!DOCTYPE html><html lang="en"><head><title>phpunit</title></head><body><h1>phpunit</h1></body></html>',
            static::SECRET,
            false
        );
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testResponsesWith400IfActionIsMissing()
    {
        $request  = $this->getConnectRequest([]);
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testResponsesWith404IfActionIsUnknown()
    {
        $request  = $this->getConnectRequest(['action' => 'UnknownAction']);
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCanGetLanguagesFromShop()
    {
        $request  = $this->getConnectRequest(['action' => 'listLanguages']);
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['de', 'en'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testFetchChangesFromShop()
    {
        /** @var Repository $repo */
        $repo = static::getContainer()->get(Repository::class);
        $repo->touchAll();

        for ($since = 0;; $since += 25) {
            $body    = [
                'action' => 'getUpdates',
                'since'  => $since,
                'count'  => 25,
            ];
            $request = $this->getConnectRequest($body);

            $controller  = new Endpoint();
            $rawResponse = $controller->handleRequest($request);
            $response    = json_decode($rawResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $this->assertSnapshot($response, null, true);
            if ($response['count'] === 0) {
                break;
            }
        }
    }
}

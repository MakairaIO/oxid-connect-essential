<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Controller;

use Makaira\OxidConnectEssential\Controller\Endpoint;
use Makaira\OxidConnectEssential\Repository;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;

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

        $body = [
            'action' => 'getUpdates',
            'since'  => 0,
            'count'  => 1000,
        ];
        $request = $this->getConnectRequest($body);

        $controller = new Endpoint();
        $rawResponse = $controller->handleRequest($request);
        $response = json_decode($rawResponse->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertSnapshot($response);
    }
}

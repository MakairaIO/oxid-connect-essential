<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Controller;

use Makaira\OxidConnectEssential\Repository;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;

use function json_encode;

class EndpointTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::setModuleSetting('makaira_connect_secret', parent::SECRET);
    }

    public function testResponsesWith403ForInvalidSignature()
    {
        $client = $this->getConnectClient('s3cr3t');
        $response = $client->request(['action' => 'listLanguages']);

        $this->assertEquals(403, $response->status);
    }

    public function testCanGetLanguagesFromShop()
    {
        $client = $this->getConnectClient();
        $response = $client->request(['action' => 'listLanguages']);

        $this->assertSame(200, $response->status);
        $this->assertSame(['de', 'en'], $response->body);
    }

    public function testFetchChangesFromShop()
    {
        /** @var Repository $repo */
        $repo = static::getContainer()->get(Repository::class);
        $repo->touchAll();

        $client = $this->getConnectClient();
        $response = $client->request(
            [
                'action' => 'getUpdates',
                'since' => 0,
                'count' => 1000,
            ]
        );

        $this->assertSame(200, $response->status, json_encode($response->body, JSON_THROW_ON_ERROR));
        $this->assertSnapshot($response->body);
    }
}

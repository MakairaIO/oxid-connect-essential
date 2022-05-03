<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Controller;

use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;

use OxidEsales\Eshop\Core\Registry;

use function json_encode;
use function var_export;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class EndpointTest extends IntegrationTestCase
{
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
}

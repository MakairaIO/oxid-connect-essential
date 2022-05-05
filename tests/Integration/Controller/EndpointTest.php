<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Controller;

use Makaira\OxidConnectEssential\Controller\Endpoint;
use Makaira\OxidConnectEssential\Repository;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use OxidEsales\Eshop\Application\Model\Attribute;
use OxidEsales\Eshop\Core\Model\BaseModel;
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
        $this->prepareProducts();
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

            if ($response['count'] === 0) {
                break;
            }

            $this->assertSnapshot($response, null, true);
        }
    }

    private function prepareProducts()
    {
        $testProductId    = '6b6c129c62119185c7779987e7d8cd5c';
        $intAttributeId   = md5('phphunit_attribute_int');
        $floatAttributeId = md5('phphunit_attribute_float');

        $intAttribute = new Attribute();
        $intAttribute->assign(
            [
                'oxid'     => $intAttributeId,
                'oxtitle'  => 'PHPUnit integer attribute',
                'oxshopid' => 1,
            ]
        );
        $intAttribute->save();

        $floatAttribute = new Attribute();
        $floatAttribute->assign(
            [
                'oxid'     => $floatAttributeId,
                'oxtitle'  => 'PHPUnit float attribute',
                'oxshopid' => 1,
            ]
        );
        $floatAttribute->save();

        $articleAttributeInt = new BaseModel();
        $articleAttributeInt->init('oxobject2attribute');
        $articleAttributeInt->assign(
            [
                'oxid'       => md5("{$testProductId}-{$intAttributeId}"),
                'oxobjectid' => $testProductId,
                'oxattrid'   => $intAttributeId,
                'oxvalue'    => '42',
            ]
        );
        $articleAttributeInt->save();

        $articleAttributeFloat = new BaseModel();
        $articleAttributeFloat->init('oxobject2attribute');
        $articleAttributeFloat->assign(
            [
                'oxid'       => md5("{$testProductId}-{$floatAttributeId}"),
                'oxobjectid' => $testProductId,
                'oxattrid'   => $floatAttributeId,
                'oxvalue'    => '4.2',
            ]
        );
        $articleAttributeInt->save();

        self::setModuleSetting('makaira_attribute_as_int', [$intAttributeId]);
        self::setModuleSetting('makaira_attribute_as_float', [$floatAttributeId]);

        /** @var Repository $repo */
        $repo = static::getContainer()->get(Repository::class);
        $repo->touchAll();
    }
}

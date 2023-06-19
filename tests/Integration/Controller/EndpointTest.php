<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Controller;

use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Test\ArraySort;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use JsonException;
use Makaira\OxidConnectEssential\Command\TouchAllCommand;
use Makaira\OxidConnectEssential\Controller\Endpoint;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Attribute;
use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;

use function array_filter;
use function array_keys;
use function array_map;
use function cubrid_num_cols;
use function end;
use function is_numeric;
use function is_string;
use function json_decode;
use function md5;
use function preg_match;
use function preg_replace;

use const JSON_THROW_ON_ERROR;

/**
 * @SuppressWarnings(PHPMD)
 */
class EndpointTest extends IntegrationTestCase
{
    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        parent::setUp();

        $moduleSettings = $this->getService(ModuleSettingServiceInterface::class);
        $moduleSettings->saveString('makaira_connect_secret', static::SECRET, static::MODULE_ID);
    }

    /**
     * @return void
     * @throws JsonException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testResponsesWith403ForInvalidSignature(): void
    {
        $request  = $this->getConnectRequest(['action' => 'listLanguages'], 's3cr3t');
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testResponsesWith401IfHeadersAreMissing(): void
    {
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest(new Request());

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function testResponsesWith400IfBodyIsNotJson(): void
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

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function testResponsesWith400IfActionIsMissing(): void
    {
        $request  = $this->getConnectRequest([]);
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function testResponsesWith404IfActionIsUnknown(): void
    {
        $request  = $this->getConnectRequest(['action' => 'UnknownAction']);
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function testResponsesWith400IfSinceIsMissing(): void
    {
        $request  = $this->getConnectRequest(['action' => 'getUpdates','count' => 25]);
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function testCanGetLanguagesFromShop(): void
    {
        $request  = $this->getConnectRequest(['action' => 'listLanguages']);
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['de', 'en'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws ExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function testCanGetReplicationStatus(): void
    {
        $this->touchAll();

        $request  = $this->getConnectRequest(
            [
                'action'  => 'getReplicationStatus',
                'indices' => [
                    'de' => [
                        'lastRevision' => 250,
                    ],
                    'en' => [
                        'lastRevision' => 250,
                    ],
                ],
            ]
        );
        $endpoint = new Endpoint();
        $response = $endpoint->handleRequest($request);

        $this->assertSame(200, $response->getStatusCode());

        $actual = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('de', $actual);
        $this->assertArrayHasKey('lastRevision', $actual['de']);
        $this->assertArrayHasKey('openChanges', $actual['de']);
        $this->assertGreaterThanOrEqual(250, $actual['de']['lastRevision']);
        $this->assertGreaterThanOrEqual(7, $actual['de']['openChanges']);

        $this->assertArrayHasKey('en', $actual);
        $this->assertArrayHasKey('lastRevision', $actual['en']);
        $this->assertArrayHasKey('openChanges', $actual['en']);
        $this->assertGreaterThanOrEqual(250, $actual['en']['lastRevision']);
        $this->assertGreaterThanOrEqual(7, $actual['en']['openChanges']);
    }

    /**
     * @param string $language
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws ExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     * @dataProvider provideLanguages
     */
    public function testFetchChangesFromShop(string $language): void
    {
        $this->prepareProducts();

        $since = 0;
        do {
            $body    = [
                'action'   => 'getUpdates',
                'since'    => $since,
                'count'    => 25,
                'language' => $language,
            ];
            $request = $this->getConnectRequest($body);

            $controller  = new Endpoint();
            $rawResponse = $controller->handleRequest($request);
            static::assertLessThan(400, $rawResponse->getStatusCode(), $rawResponse->getContent());
            $response    = json_decode($rawResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if ($response['count'] > 0) {
                $response['changes'] = array_map(
                    static function (array $change) {
                        $change['data']['timestamp'] = preg_replace('/\d/', 'X', $change['data']['timestamp']);
                        $change['data']['insert']    = preg_replace('/\d/', 'X', $change['data']['insert']);
                        $change['data']['url']       = preg_replace('/^.*$/', 'X', $change['data']['url']);

                        $boostFields = array_filter(
                            array_keys($change['data']),
                            static fn ($key) => str_starts_with($key, 'mak_boost_')
                        );

                        foreach ($boostFields as $boostField) {
                            if (is_numeric($change['data'][$boostField])) {
                                $change['data'][$boostField] = 'X';
                            }
                        }

                        if (
                            isset($change['data']['picture_url_main']) &&
                            is_string($change['data']['picture_url_main'])
                        ) {
                            $change['data']['picture_url_main'] = preg_replace(
                                '@^.*(/out/pictures/)@',
                                '$1',
                                $change['data']['picture_url_main']
                            );
                        }

                        if (isset($change['data']['additionalData'])) {
                            unset($change['data']['additionalData']);
                        }

                        if (isset($change['data']['shop'])) {
                            $change['data']['shop'] = array_map(
                                static fn ($shopId) => (int) $shopId,
                                (array) $change['data']['shop']
                            );
                        }

                        if (isset($change['data']['attributeStr'])) {
                            $change['data']['attributeStr'] = ArraySort::mergeSort(
                                $change['data']['attributeStr'],
                                ['title' => ArraySort::ASCENDING, 'value' => ArraySort::ASCENDING]
                            );
                        }

                        ksort($change['data']);

                        return $change;
                    },
                    $response['changes']
                );

                $this->assertSnapshot($response, null, true);
                $lastChange = end($response['changes']);
                $since      = $lastChange['sequence'];
            }
        } while ($response['count'] > 0);

        $this->assertGreaterThan(0, $since, sprintf('No changes were returned for language "%s".', $language));
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideLanguages(): array
    {
        return [
            'Changes in german'  => ['de'],
            'Changes in english' => ['en'],
        ];
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function prepareProducts(): void
    {
        $testProductId    = '6b63f459c781fa42edeb889242304014';
        $testVariantId    = '6b6c129c62119185c7779987e7d8cd5c';
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
        $intAttribute->setLanguage(0);
        $intAttribute->save();
        $intAttribute->setLanguage(1);
        $intAttribute->save();

        $floatAttribute = new Attribute();
        $floatAttribute->assign(
            [
                'oxid'     => $floatAttributeId,
                'oxtitle'  => 'PHPUnit float attribute',
                'oxshopid' => 1,
            ]
        );
        $floatAttribute->setLanguage(0);
        $floatAttribute->save();
        $floatAttribute->setLanguage(1);
        $floatAttribute->save();

        $articleAttributeInt = new MultiLanguageModel();
        $articleAttributeInt->init('oxobject2attribute');
        $articleAttributeInt->assign(
            [
                'oxid'       => md5("{$testProductId}-{$intAttributeId}"),
                'oxobjectid' => $testProductId,
                'oxattrid'   => $intAttributeId,
                'oxvalue'    => '21',
            ]
        );
        $articleAttributeInt->setLanguage(0);
        $articleAttributeInt->save();
        $articleAttributeInt->setLanguage(1);
        $articleAttributeInt->save();

        $productAttributeFloat = new MultiLanguageModel();
        $productAttributeFloat->init('oxobject2attribute');
        $productAttributeFloat->assign(
            [
                'oxid'       => md5("{$testProductId}-{$floatAttributeId}"),
                'oxobjectid' => $testProductId,
                'oxattrid'   => $floatAttributeId,
                'oxvalue'    => '2.1',
            ]
        );
        $productAttributeFloat->setLanguage(0);
        $productAttributeFloat->save();
        $productAttributeFloat->setLanguage(1);
        $productAttributeFloat->save();

        $articleAttributeInt = new MultiLanguageModel();
        $articleAttributeInt->init('oxobject2attribute');
        $articleAttributeInt->assign(
            [
                'oxid'       => md5("{$testVariantId}-{$intAttributeId}"),
                'oxobjectid' => $testVariantId,
                'oxattrid'   => $intAttributeId,
                'oxvalue'    => '42',
            ]
        );
        $articleAttributeInt->setLanguage(0);
        $articleAttributeInt->save();
        $articleAttributeInt->setLanguage(1);
        $articleAttributeInt->save();

        $productAttributeFloat = new MultiLanguageModel();
        $productAttributeFloat->init('oxobject2attribute');
        $productAttributeFloat->assign(
            [
                'oxid'       => md5("{$testVariantId}-{$floatAttributeId}"),
                'oxobjectid' => $testVariantId,
                'oxattrid'   => $floatAttributeId,
                'oxvalue'    => '4.2',
            ]
        );
        $productAttributeFloat->setLanguage(0);
        $productAttributeFloat->save();
        $productAttributeFloat->setLanguage(1);
        $productAttributeFloat->save();

        $product = new Article();
        $product->assign(
            [
                'oxid'        => md5('PHPUnit test product-Product with OXSTOCKFLAG = 4'),
                'oxactive'    => '1',
                'oxtitle'     => 'PHPUnit test product',
                'oxshortdesc' => 'Product with OXSTOCKFLAG = 4',
                'oxstockflag' => '4',
            ]
        );
        $product->setLanguage(0);
        $product->save();
        $product->setLanguage(1);
        $product->save();

        $moduleSettings = $this->getService(ModuleSettingServiceInterface::class);
        $moduleSettings->saveCollection('makaira_attribute_as_int', [$intAttributeId], static::MODULE_ID);
        $moduleSettings->saveCollection('makaira_attribute_as_float', [$floatAttributeId], static::MODULE_ID);

        $this->touchAll();
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function touchAll(): void
    {
        $database = $this->getService(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();

        $database->executeQuery('TRUNCATE makaira_connect_changes');
        $database->executeQuery('ALTER TABLE makaira_connect_changes AUTO_INCREMENT = 1');

        $repo = $this->getService(TouchAllCommand::class);
        $repo->run(new ArrayInput([]), new NullOutput());
    }
}

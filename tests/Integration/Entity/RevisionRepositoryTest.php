<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Entity;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class RevisionRepositoryTest extends IntegrationTestCase
{
    /**
     * @return void
     * @throws ConnectionException
     * @throws ContainerExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     */
    public function testWriteRevisions(): void
    {
        $repository = $this->insertRevisions();

        $revisions = $repository->getRevisions(0);
        $this->normalizeRevisions($revisions);

        $expected = [
            ['sequence' => 1, 'id' => 'product_21', 'type' => 'product'],
            ['sequence' => 2, 'id' => 'category_42', 'type' => 'category'],
            ['sequence' => 3, 'id' => 'manufacturer_84', 'type' => 'manufacturer'],
            ['sequence' => 4, 'id' => 'custom_168', 'type' => 'custom'],
        ];

        static::assertSame($expected, $revisions);

        static::assertSame($expected, $revisions);
    }

    /**
     * @return void
     * @throws ConnectionException
     * @throws ContainerExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     */
    public function testRevisionsAreRemoved(): void
    {
        $repository = $this->insertRevisions();

        $db = $this->getService(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();

        $query = <<<'EOQ'
REPLACE INTO `makaira_connect_changes` (`TYPE`, `OXID`, `CHANGED`)
VALUES (:type, :id1, :changed1), (:type, :id2, :changed2)
EOQ;

        $db->executeQuery(
            $query,
            [
                'type' => 'custom',
                'id1' => 'custom_1',
                'id2' => 'custom_2',
                'changed1' => '2022-01-01T00:00:00.000+00:00',
                'changed2' => '2021-01-01T00:00:00.000+00:00',
            ]
        );

        $revisions = $repository->getRevisions(0);
        $this->normalizeRevisions($revisions);

        $expected = [
            ['sequence' => 1, 'id' => 'product_21', 'type' => 'product'],
            ['sequence' => 2, 'id' => 'category_42', 'type' => 'category'],
            ['sequence' => 3, 'id' => 'manufacturer_84', 'type' => 'manufacturer'],
            ['sequence' => 4, 'id' => 'custom_168', 'type' => 'custom'],
            ['sequence' => 5, 'id' => 'custom_1', 'type' => 'custom'],
            ['sequence' => 6, 'id' => 'custom_2', 'type' => 'custom'],
        ];

        $this->assertSame($expected, $revisions);

        $repository->cleanup();

        $revisions = $repository->getRevisions(0);
        $this->normalizeRevisions($revisions);

        $expected = [
            ['sequence' => 1, 'id' => 'product_21', 'type' => 'product'],
            ['sequence' => 2, 'id' => 'category_42', 'type' => 'category'],
            ['sequence' => 3, 'id' => 'manufacturer_84', 'type' => 'manufacturer'],
            ['sequence' => 4, 'id' => 'custom_168', 'type' => 'custom'],
        ];

        $this->assertSame($expected, $revisions);
    }

    /**
     * @return RevisionRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function insertRevisions(): RevisionRepository
    {
        $db = $this->getService(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();
        $db->executeQuery('TRUNCATE makaira_connect_changes');
        $db->executeQuery('ALTER TABLE makaira_connect_changes AUTO_INCREMENT = 1');

        $repository = $this->getService(RevisionRepository::class);

        $repository->touchProduct('product_21');
        $repository->touchcategory('category_42');
        $repository->touchManufacturer('manufacturer_84');
        $repository->touch('custom', 'custom_168');

        return $repository;
    }

    /**
     * @param array $revisions
     *
     * @return void
     */
    private function normalizeRevisions(array &$revisions): void
    {
        $revisions = array_map(
            static fn ($revision) => [...$revision, 'sequence' => (int)$revision['sequence']],
            $revisions
        );
    }
}

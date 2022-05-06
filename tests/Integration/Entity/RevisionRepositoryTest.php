<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;

class RevisionRepositoryTest extends IntegrationTestCase
{
    public function testWriteRevisions(): void
    {
        $repository = $this->insertRevisions();

        $revisions = $repository->getRevisions(0);

        $expected = [
            ['sequence' => '1', 'id' => 'product_21', 'type' => 'product'],
            ['sequence' => '2', 'id' => 'category_42', 'type' => 'category'],
            ['sequence' => '3', 'id' => 'manufacturer_84', 'type' => 'manufacturer'],
            ['sequence' => '4', 'id' => 'custom_168', 'type' => 'custom'],
        ];

        $this->assertSame($expected, $revisions);

        $this->assertSame($expected, $revisions);
    }

    public function testRevisionsAreRemoved(): void
    {
        $repository = $this->insertRevisions();

        $container = static::getContainer();
        $db        = $container->get(Connection::class);

        $query = <<<'EOQ'
REPLACE INTO `makaira_connect_changes` (`TYPE`, `OXID`, `CHANGED`)
VALUES (:type, :id1, :changed1), (:type, :id2, :changed2)
EOQ;

        $db->executeQuery(
            $query,
            [
                'type'     => 'custom',
                'id1'      => 'custom_1',
                'id2'      => 'custom_2',
                'changed1' => '2022-01-01T00:00:00.000+00:00',
                'changed2' => '2021-01-01T00:00:00.000+00:00',
            ]
        );

        $revisions = $repository->getRevisions(0);

        $expected = [
            ['sequence' => '1', 'id' => 'product_21', 'type' => 'product'],
            ['sequence' => '2', 'id' => 'category_42', 'type' => 'category'],
            ['sequence' => '3', 'id' => 'manufacturer_84', 'type' => 'manufacturer'],
            ['sequence' => '4', 'id' => 'custom_168', 'type' => 'custom'],
            ['sequence' => '5', 'id' => 'custom_1', 'type' => 'custom'],
            ['sequence' => '6', 'id' => 'custom_2', 'type' => 'custom'],
        ];

        $this->assertSame($expected, $revisions);

        $repository->cleanup();

        $revisions = $repository->getRevisions(0);

        $expected = [
            ['sequence' => '1', 'id' => 'product_21', 'type' => 'product'],
            ['sequence' => '2', 'id' => 'category_42', 'type' => 'category'],
            ['sequence' => '3', 'id' => 'manufacturer_84', 'type' => 'manufacturer'],
            ['sequence' => '4', 'id' => 'custom_168', 'type' => 'custom'],
        ];

        $this->assertSame($expected, $revisions);
    }

    /**
     * @return RevisionRepository
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function insertRevisions(): RevisionRepository
    {
        $container = static::getContainer();
        $db        = $container->get(Connection::class);
        $db->executeQuery('TRUNCATE makaira_connect_changes');
        $db->executeQuery('ALTER TABLE makaira_connect_changes AUTO_INCREMENT = 1');

        /** @var RevisionRepository $repository */
        $repository = $container->get(RevisionRepository::class);

        $repository->touchProduct('product_21');
        $repository->touchcategory('category_42');
        $repository->touchManufacturer('manufacturer_84');
        $repository->touch('custom', 'custom_168');

        return $repository;
    }
}

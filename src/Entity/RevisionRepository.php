<?php

namespace Makaira\OxidConnectEssential\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class RevisionRepository
{
    private Connection $connection;

    private ?Statement $fetchParentId = null;

    private ?Statement $insertRevision = null;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return void
     * @throws ConnectionException
     */
    public function touch(string $type, string $id): void
    {
        $this->storeRevisions([new Revision($type, $id)]);
    }

    /**
     * @param string $id
     *
     * @return void
     * @throws DBALException
     * @throws DBALDriverException
     */
    public function touchProduct(string $id): void
    {
        if (null === $this->fetchParentId) {
            $this->fetchParentId = $this->connection->prepare('SELECT `OXPARENTID` FROM `oxarticles` WHERE `OXID` = ?');
        }

        $result = $this->fetchParentId->executeQuery([$id]);

        $isVariant = (bool) $result->fetchOne();
        $this->touch($isVariant ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT, $id);
    }

    /**
     * @param string $id
     *
     * @return void
     * @throws ConnectionException
     */
    public function touchCategory(string $id): void
    {
        $this->touch(Revision::TYPE_CATEGORY, $id);
    }

    /**
     * @param string $id
     *
     * @return void
     * @throws ConnectionException
     */
    public function touchManufacturer(string $id): void
    {
        $this->touch(Revision::TYPE_MANUFACTURER, $id);
    }

    /**
     * Registers changes in one transaction.
     *
     * @param array<Revision> $revisions
     *
     * @return void
     * @throws ConnectionException
     */
    public function storeRevisions(array $revisions): void
    {
        $this->connection->beginTransaction();
        try {
            if (null === $this->insertRevision) {
                $this->insertRevision = $this->connection->prepare(
                    'REPLACE INTO `makaira_connect_changes` (`TYPE`, `OXID`) VALUES (:type, :objectId)'
                );
            }

            foreach ($revisions as $revision) {
                $this->insertRevision->executeStatement(['type' => $revision->type, 'objectId' => $revision->objectId]);
            }

            $this->connection->commit();
        } catch (DBALException | DBALDriverException) {
            $this->connection->rollBack();
        }
    }

    /**
     * @param int $since
     *
     * @return int
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function countChanges(int $since): int
    {
        $prepared = $this->connection->prepare('SELECT COUNT(*) FROM `makaira_connect_changes` WHERE `SEQUENCE` > ?');
        $result = $prepared->executeQuery([$since]);

        /** @var string $count */
        $count = $result->fetchOne();

        return (int) $count;
    }

    /**
     * @param int $since
     * @param int $limit
     *
     * @return array
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function getRevisions(int $since, int $limit = 50): array
    {
        $prepared = $this->connection->prepare(
            'SELECT `SEQUENCE` as sequence, `OXID` as id, `TYPE` as type
            FROM `makaira_connect_changes`
            WHERE `SEQUENCE` > :since
            ORDER BY `SEQUENCE` ASC
            LIMIT :limit'
        );

        $prepared->bindValue(':since', $since, ParameterType::INTEGER);
        $prepared->bindValue(':limit', $limit, ParameterType::INTEGER);

        return $prepared->executeQuery()->fetchAllAssociative();
    }

    /**
     * @return void
     * @throws DBALException
     */
    public function cleanup(): void
    {
        $this->connection->executeQuery(
            'DELETE FROM
              makaira_connect_changes
            WHERE
              changed < DATE_SUB(NOW(), INTERVAL 1 DAY)'
        );
    }
}

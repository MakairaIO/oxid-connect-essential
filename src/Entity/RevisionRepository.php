<?php

namespace Makaira\OxidConnectEssential\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Domain\Revision;

class RevisionRepository
{
    /**
     * @param Connection $connection
     */
    public function __construct(private Connection $connection)
    {
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
        $statement = $this->connection->prepare('SELECT OXPARENTID FROM oxarticles WHERE OXID = ?');
        $statement->execute([$id]);

        $isVariant = (bool) $statement->fetchOne();
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
            $prepared = $this->connection->prepare(
                'REPLACE INTO `makaira_connect_changes` (`TYPE`, `OXID`)
            VALUES (:type, :objectId)'
            );

            $prepared->bindParam('type', $type);
            $prepared->bindParam('objectId', $objectId);

            foreach ($revisions as $revision) {
                $type     = $revision->type;
                $objectId = $revision->objectId;
                $prepared->execute();
            }

            $this->connection->commit();
        } catch (DBALException | DBALDriverException) {
            $this->connection->rollBack();
        }
    }
}

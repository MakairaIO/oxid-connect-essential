<?php

namespace Makaira\OxidConnectEssential\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Change;
use Makaira\OxidConnectEssential\Event\RepositoryCollectEvent;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\TableTranslator;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractRepository
{
    protected Connection $database;

    private ModifierList $modifiers;

    private TableTranslator $tableTranslator;

    private DataMapper $dataMapper;

    /**
     * @param Connection      $database
     * @param ModifierList    $modifiers
     * @param TableTranslator $tableTranslator
     */
    public function __construct(
        Connection $database,
        ModifierList $modifiers,
        TableTranslator $tableTranslator,
        DataMapper $dataMapper
    ) {
        $this->tableTranslator = $tableTranslator;
        $this->modifiers       = $modifiers;
        $this->database        = $database;
        $this->dataMapper      = $dataMapper;
    }

    /**
     * @param Event $e
     *
     * @return void
     */
    public function addRepository(Event $e): void
    {
        if ($e instanceof RepositoryCollectEvent) {
            $e->addRepository($this);
        }
    }

    public function get(string $id): Change
    {
        /** @var Result $resultStatement */
        $resultStatement = $this->database->executeQuery($this->getSelectQuery(), ['id' => $id]);

        /** @var array<string, string> $result */
        $result = $resultStatement->fetchAssociative();

        $deleted = empty($result);
        $change  = new Change(
            [
                'id'      => $id,
                'type'    => $this->getType(),
                'deleted' => $deleted,
            ]
        );

        if (!$deleted) {
            $type = $this->getInstance($result['id']);

            $this->dataMapper->map($type, $result, $this->getType());

            $change->data = $this->modifiers->applyModifiers($type, $this->getType());
        }

        return $change;
    }

    /**
     * Get all IDs handled by this repository.
     *
     * @param int|string|null $shopId
     *
     * @return array
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function getAllIds($shopId = null): array
    {
        $sql = $this->getAllIdsQuery();
        $this->tableTranslator->setShopId($shopId);
        $sql = $this->tableTranslator->translate($sql);

        /** @var Result $resultStatement */
        $resultStatement = $this->database->executeQuery($sql);

        return $resultStatement->fetchFirstColumn();
    }

    abstract public function getType(): string;

    abstract protected function getInstance(string $id): Type;

    abstract protected function getSelectQuery(): string;

    abstract protected function getAllIdsQuery(): string;

    abstract protected function getParentIdQuery(): ?string;
}

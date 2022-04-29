<?php

namespace Makaira\OxidConnectEssential\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Change;
use Makaira\OxidConnectEssential\Event\RepositoryCollectEvent;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\TableTranslator;
use OxidEsales\Eshop\Core\Model\BaseModel;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractRepository
{
    protected Connection $database;

    private ModifierList $modifiers;

    private TableTranslator $tableTranslator;

    /**
     * @param Connection      $database
     * @param ModifierList    $modifiers
     * @param TableTranslator $tableTranslator
     */
    public function __construct(
        Connection $database,
        ModifierList $modifiers,
        TableTranslator $tableTranslator
    ) {
        $this->tableTranslator = $tableTranslator;
        $this->modifiers       = $modifiers;
        $this->database        = $database;
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

    public function get($id): Change
    {
        $result = $this->database->executeQuery($this->getSelectQuery(), ['id' => $id])->fetchAssociative();

        $change       = new Change();
        $change->id   = (string) $id;
        $change->type = $this->getType();

        if (empty($result)) {
            $change->deleted = true;

            return $change;
        }

        $type         = $this->getInstance($result);
        $type         = $this->modifiers->applyModifiers($type, $this->getType());
        $change->data = $type;

        return $change;
    }

    /**
     * Get all IDs handled by this repository.
     *
     * @param null $shopId
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

        return $this->database->executeQuery($sql)->fetchFirstColumn();
    }

    abstract public function getType(): string;

    abstract protected function getInstance($id): Type;

    abstract protected function getSelectQuery(): string;

    abstract protected function getAllIdsQuery(): string;

    abstract protected function getParentIdQuery(): ?string;
}

<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Product\Product;
use Makaira\OxidConnectEssential\Utils\TableTranslator;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\UtilsObject;

abstract class AbstractActiveModifier extends Modifier
{
    private ?string $activeSnippet = null;

    private ?string $tableName = null;

    private ?BaseModel $model = null;

    /**
     * @param Connection   $connection
     * @param class-string $modelClass
     * @param UtilsObject  $utilsObject
     */
    public function __construct(
        private Connection $connection,
        private string $modelClass,
        private UtilsObject $utilsObject,
        private TableTranslator $tableTranslator
    ) {
    }

    /**
     * Modify product and return modified product
     *
     * @param Product $product
     *
     * @return Type
     * @throws DBALException
     * @throws DBALDriverException
     * @throws SystemComponentException
     */
    public function apply(Type $product): Type
    {
        $this->safeGuard();

        $sql = "SELECT COUNT(OXID) > 0
            FROM {$this->tableName}
            WHERE OXID = '{$product->id}' AND {$this->activeSnippet}";

        /** @var Result $resultStatement */
        $resultStatement = $this->connection->executeQuery(
            $this->tableTranslator->translate($sql)
        );
        $product->active = (bool)$resultStatement->fetchOne();

        return $product;
    }

    /**
     * @return void
     * @throws SystemComponentException
     */
    protected function safeGuard(): void
    {
        if (
            !($this->model instanceof BaseModel) &&
            (($model = $this->utilsObject->oxNew($this->modelClass)) instanceof BaseModel)
        ) {
            $this->model = $model;
        }

        if (!$this->activeSnippet && $this->model instanceof BaseModel) {
            $this->activeSnippet = $this->model->getSqlActiveSnippet(true);
        }
        if (!$this->tableName && $this->model instanceof BaseModel) {
            $this->tableName = (string)$this->model->getCoreTableName();
        }
    }
}

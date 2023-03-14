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
    /**
     * @var string
     */
    private ?string $activeSnippet = null;

    /**
     * @var string
     */
    private ?string $tableName = null;

    private string $modelClass;

    private ?BaseModel $model = null;

    private Connection $database;

    private UtilsObject $utilsObject;

    private TableTranslator $tableTranslator;

    /**
     * @param Connection  $database
     * @param BaseModel   $model
     * @param UtilsObject $utilsObject
     */
    public function __construct(
        Connection $database,
        string $modelClass,
        UtilsObject $utilsObject,
        TableTranslator $tableTranslator
    ) {
        $this->database        = $database;
        $this->modelClass      = $modelClass;
        $this->utilsObject     = $utilsObject;
        $this->tableTranslator = $tableTranslator;
    }

    /**
     * Modify product and return modified product
     *
     * @param Product $product
     *
     * @return Type
     * @throws DBALException
     * @throws DBALDriverException
     */
    public function apply(Type $product)
    {
        $this->safeGuard();

        $sql = "SELECT COUNT(OXID) > 0
            FROM {$this->tableName}
            WHERE OXID = '{$product->id}' AND {$this->activeSnippet}";

        /** @var Result $resultStatement */
        $resultStatement = $this->database->executeQuery(
            $this->tableTranslator->translate($sql)
        );
        $product->active = (bool)$resultStatement->fetchOne();

        return $product;
    }

    protected function safeGuard(): void
    {
        if (!$this->model instanceof BaseModel) {
            $this->model = $this->utilsObject->oxNew($this->modelClass);
        }
        if (!$this->activeSnippet) {
            $this->activeSnippet = $this->model->getSqlActiveSnippet(true);
        }
        if (!$this->tableName) {
            $this->tableName = (string) $this->model->getCoreTableName();
        }
    }
}

<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Product\Product;
use OxidEsales\Eshop\Core\Model\BaseModel;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;


abstract class AbstractActiveModifier extends Modifier
{
    use SymfonyContainerTrait;

    /**
     * @var string
     */
    private $activeSnippet = null;

    /**
     * @var string
     */
    private $tableName = null;

    private string $modelClass;

    /**
     * @var BaseModel
     */
    private $model = null;

    private Connection $database;

    /**
     * @param Connection $database
     * @param BaseModel  $model
     */
    public function __construct(Connection $database, string $modelClass)
    {
        $this->database   = $database;
        $this->modelClass = $modelClass;
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
        $resultStatement = $this->database->executeQuery($sql);
        $product->active = (bool) $resultStatement->fetchOne();

        return $product;
    }

    protected function safeGuard(): void
    {
        if (!$this->model instanceof BaseModel) {
            $this->model = $this->getSymfonyContainer()->get(\OxidEsales\Eshop\Core\UtilsObject::class)
                ->oxNew($this->modelClass);
        }
        if (!$this->activeSnippet) {
            $this->activeSnippet = $this->model->getSqlActiveSnippet(true);
        }
        if (!$this->tableName) {
            $this->tableName = (string) $this->model->getCoreTableName();
        }
    }
}

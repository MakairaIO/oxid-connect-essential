<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Product\Product;
use OxidEsales\Eshop\Core\Model\BaseModel;

abstract class AbstractActiveModifier extends Modifier
{
    /**
     * @var string
     */
    private string $activeSnippet;

    /**
     * @var string
     */
    private string $tableName;

    /**
     * @param Connection $database
     * @param BaseModel  $model
     */
    public function __construct(private Connection $database, private BaseModel $model)
    {
        $this->activeSnippet = $this->model->getSqlActiveSnippet(true);
        $this->tableName     = (string) $this->model->getCoreTableName();
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
        $sql = "SELECT COUNT(OXID) > 0
            FROM {$this->tableName}
            WHERE OXID = '{$product->id}' AND {$this->activeSnippet}";

        $product->active = (bool) $this->database->executeQuery($sql)->fetchFirstColumn();

        return $product;
    }
}

<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

use function array_column;
use function sprintf;

abstract class AbstractShopModifier extends Modifier
{
    private const QUERY_TEMPLATE = 'SELECT
          `OXSHOPID`
        FROM
          `%s`
        WHERE
          `OXMAPOBJECTID` = ?';

    private string $selectQuery;

    private Connection $database;

    private bool $isMultiShop;

    /**
     * @param Connection $database
     * @param bool       $isMultiShop
     */
    public function __construct(Connection $database, bool $isMultiShop)
    {
        $this->isMultiShop = $isMultiShop;
        $this->database    = $database;
        $this->selectQuery = sprintf(self::QUERY_TEMPLATE, $this->getTableName());
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     * @throws DBALException
     */
    public function apply(Type $type)
    {
        if ($this->isMultiShop) {
            /** @var Result $resultStatement */
            $resultStatement = $this->database->executeQuery($this->selectQuery, [$type->additionalData['OXMAPID']]);
            $type->shop      = $resultStatement->fetchFirstColumn();
        } else {
            $type->shop = [$type->additionalData['OXSHOPID']];
        }

        return $type;
    }

    /**
     * @return string
     */
    abstract protected function getTableName(): string;
}

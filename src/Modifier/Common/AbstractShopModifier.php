<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

use function array_column;
use function sprintf;

/**
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
abstract class AbstractShopModifier extends Modifier
{
    private const QUERY_TEMPLATE = 'SELECT
          `OXSHOPID`
        FROM
          `%s`
        WHERE
          `OXMAPOBJECTID` = ?';

    private string $selectQuery;

    /**
     * @param Connection $connection
     * @param bool       $isMultiShop
     */
    public function __construct(private Connection $connection, private bool $isMultiShop)
    {
        $this->selectQuery = sprintf(self::QUERY_TEMPLATE, $this->getTableName());
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     * @throws DBALException
     * @throws DBALDriverException
     */
    public function apply(Type $type)
    {
        if ($this->isMultiShop) {
            /** @var Result $resultStatement */
            $resultStatement = $this->connection->executeQuery($this->selectQuery, [$type->additionalData['OXMAPID']]);
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

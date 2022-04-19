<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

use function array_column;
use function sprintf;

abstract class AbstractShopModifier extends Modifier
{
    private const SHOP_FIELD_SET_SIZE = 64;

    private const QUERY_TEMPLATE = 'SELECT
          `OXSHOPID`
        FROM
          `%s`
        WHERE
          `OXMAPOBJECTID` = ?';

    private string $selectQuery;

    /**
     * @param Connection $database
     * @param bool       $isMultiShop
     */
    public function __construct(private Connection $database, private bool $isMultiShop)
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
     */
    public function apply(Type $type)
    {
        if ($this->isMultiShop) {
            $type->shop = $this->database
                ->executeQuery($this->selectQuery, [$type->OXMAPID])
                ->fetchFirstColumn();
        } else {
            $type->shop = [$type->OXSHOPID];
        }

        return $type;
    }

    /**
     * @return string
     */
    abstract protected function getTableName(): string;
}

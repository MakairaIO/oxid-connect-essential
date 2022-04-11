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
            if (empty($type->OXMAPID)) {
                $bitmask = $type->OXSHOPINCL;
                $type->shop = $this->getArrayFromBitmask($bitmask);
            } else {
                $type->shop = $this->database->query($this->selectQuery, ['mapId' => $type->OXMAPID]);
                $type->shop = array_column($type->shop, 'OXSHOPID');
            }
        } else {
            $type->shop = [$type->OXSHOPID];
        }

        return $type;
    }

    /**
     * @param $bitmask
     *
     * @return array
     */
    private function getArrayFromBitmask($bitmask): array
    {
        $retArray = [];
        for ($i = 0; $i < self::SHOP_FIELD_SET_SIZE; $i++) {
            if (($bitmask >> $i) & 1) {
                $retArray[] = $i + 1;
            }
        }

        return $retArray;
    }

    abstract protected function getTableName(): string;
}

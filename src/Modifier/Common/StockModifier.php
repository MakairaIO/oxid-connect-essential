<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

class StockModifier extends Modifier
{
    private Connection $database;

    private string $tableName;

    private bool $useStock;

    /**
     * @param Connection $database
     * @param string     $tableName
     * @param bool       $useStock
     */
    public function __construct(Connection $database, string $tableName, bool $useStock)
    {
        $this->useStock  = $useStock;
        $this->tableName = $tableName;
        $this->database  = $database;
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $type)
    {
        $stockFlag = 1;
        $stock     = 1;
        $onStock   = true;

        if ($this->useStock) {
            if (!isset($type->OXSTOCKFLAG, $type->OXSTOCK, $type->OXVARSTOCK)) {
                $sql    = "SELECT OXPARENTID, OXSTOCKFLAG, OXSTOCK, OXVARSTOCK FROM {$this->tableName} WHERE OXID = ?";
                $result = $this->database->executeQuery($sql, [$type->id])->fetchAssociative();
                if ($result) {
                    $stockFlag = (int) $result['OXSTOCKFLAG'];
                    $stock     = $result['OXSTOCK'] + $result['OXVARSTOCK'];
                }
            } else {
                $stockFlag = (int) $type->OXSTOCKFLAG;
                $stock     = $type->OXSTOCK + $type->OXVARSTOCK;
            }

            // 1 --> Standard
            // 2 --> Wenn ausverkauft offline
            // 3 --> Wenn ausverkauft nicht bestellbar
            // 4 --> Fremdlager
            $onStock = (2 !== $stockFlag) || (0 < $stock);
            if (4 === $stockFlag) {
                $stock = 1;
            }
        }

        $type->onstock = $onStock;
        $type->stock   = $stock;

        return $type;
    }
}

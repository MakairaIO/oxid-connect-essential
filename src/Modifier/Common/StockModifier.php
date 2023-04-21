<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Product\Product;

class StockModifier extends Modifier
{
    /**
     * @param bool $useStock
     */
    public function __construct(private bool $useStock)
    {
    }

    /**
     * Modify product and return modified product
     *
     * @param Product $type
     *
     * @return Type
     */
    public function apply(Type $type)
    {
        if ($this->useStock) {
            $stockFlag = (int) $type->additionalData['OXSTOCKFLAG'];
            $stock     = (int) $type->additionalData['OXSTOCK'] + (int) $type->additionalData['OXVARSTOCK'];

            // 1 --> Default
            // 2 --> If sold-out, go offline
            // 3 --> If sold-out, become not purchasable
            // 4 --> Handled externally
            $onStock = (2 !== $stockFlag) || (0 < $stock);
            if (4 === $stockFlag) {
                $stock = 1;
            }

            $type->onstock = $onStock;
            $type->stock   = $stock;
        }

        return $type;
    }
}

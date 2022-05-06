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
    private bool $useStock;

    /**
     * @param bool $useStock
     */
    public function __construct(bool $useStock)
    {
        $this->useStock = $useStock;
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

            // 1 --> Standard
            // 2 --> Wenn ausverkauft offline
            // 3 --> Wenn ausverkauft nicht bestellbar
            // 4 --> Fremdlager
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

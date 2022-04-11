<?php

namespace Makaira\OxidConnectEssential\Modifier\Manufacturer;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractShopModifier;

class ShopModifier extends AbstractShopModifier
{
    protected function getTableName(): string
    {
        return 'oxmanufacturers2shop';
    }
}

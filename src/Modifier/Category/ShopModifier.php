<?php

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractShopModifier;

class ShopModifier extends AbstractShopModifier
{
    protected function getTableName(): string
    {
        return 'oxcategories2shop';
    }
}

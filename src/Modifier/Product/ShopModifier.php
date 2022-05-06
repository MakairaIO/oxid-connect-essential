<?php

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractShopModifier;

class ShopModifier extends AbstractShopModifier
{
    protected function getTableName(): string
    {
        return 'oxarticles2shop';
    }
}

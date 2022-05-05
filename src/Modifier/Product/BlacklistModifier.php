<?php

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractBlacklistModifier;

class BlacklistModifier extends AbstractBlacklistModifier
{
    protected function getBlacklistedFieldSetting(): string
    {
        return 'makaira_field_blacklist_product';
    }
}

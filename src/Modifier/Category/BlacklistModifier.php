<?php

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractBlacklistModifier;

class BlacklistModifier extends AbstractBlacklistModifier
{
    protected function getBlacklistedFieldSetting(): string
    {
        return 'makaira_field_blacklist_category';
    }
}

<?php

namespace Makaira\OxidConnectEssential\Modifier\Manufacturer;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractBlacklistModifier;

class BlacklistModifier extends AbstractBlacklistModifier
{
    protected function getBlacklistedFieldSetting(): string
    {
        return 'makaira_field_blacklist_manufacturer';
    }
}

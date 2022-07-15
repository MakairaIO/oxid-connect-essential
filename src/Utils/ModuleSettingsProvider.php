<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;

class ModuleSettingsProvider
{
    /**
     * @TODO Keep this in sync with the ID in the metadata.php
     */
    public const MODULE_ID = 'makaira_oxid-connect-essential';

    private ModuleSettingBridgeInterface $moduleSettings;

    /**
     * @param ModuleSettingBridgeInterface $moduleSettings
     */
    public function __construct(ModuleSettingBridgeInterface $moduleSettings)
    {
        $this->moduleSettings = $moduleSettings;
    }

    /**
     * @param string $settingId
     *
     * @return string|int|float|bool|array<string|int|float|bool>
     */
    public function get(string $settingId)
    {
        return $this->moduleSettings->get($settingId, self::MODULE_ID);
    }
}

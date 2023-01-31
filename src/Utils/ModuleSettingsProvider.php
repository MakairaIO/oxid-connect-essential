<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;

class ModuleSettingsProvider
{
    /**
     * @TODO Keep this in sync with the ID in the metadata.php
     */
    public const MODULE_ID = 'makaira_oxid-connect-essential';

    private const SETTING_TYPES = [
        'makaira_connect_secret'               => 'getString',
        'makaira_connect_category_inheritance' => 'getBoolean',
        'makaira_field_blacklist_product'      => 'getCollection',
        'makaira_field_blacklist_category'     => 'getCollection',
        'makaira_field_blacklist_manufacturer' => 'getCollection',
        'makaira_attribute_as_int'             => 'getCollection',
        'makaira_attribute_as_float'           => 'getCollection',
        'makaira_tracking_page_id'             => 'getString',
    ];

    /**
     * @param ModuleSettingServiceInterface $moduleSettings
     */
    public function __construct(private ModuleSettingServiceInterface $moduleSettings)
    {
    }

    /**
     * @param string $settingId
     *
     * @return string|int|float|bool|array<string|int|float|bool>
     */
    public function get(string $settingId): array|float|bool|int|string
    {
        $method = self::SETTING_TYPES[$settingId] ?? 'getString';

        return $this->moduleSettings->{$method}($settingId, self::MODULE_ID);
    }
}

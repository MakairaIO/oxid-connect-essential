<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;

abstract class AbstractBlacklistModifier extends Modifier
{
    /**
     * @param ModuleSettingsProvider $moduleSettingsProvider
     */
    public function __construct(private ModuleSettingsProvider $moduleSettingsProvider)
    {
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     */
    public function apply(Type $type)
    {
        $blacklistedFields = (array) $this->moduleSettingsProvider->get($this->getBlacklistedFieldSetting());
        foreach ($blacklistedFields as $blacklistedField) {
            if (isset($type->$blacklistedField)) {
                unset($type->$blacklistedField);
            }
        }

        return $type;
    }

    /**
     * @return string
     */
    abstract protected function getBlacklistedFieldSetting(): string;
}

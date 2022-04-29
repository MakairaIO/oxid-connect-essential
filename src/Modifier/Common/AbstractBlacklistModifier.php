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

abstract class AbstractBlacklistModifier extends Modifier
{
    private array $blacklistedFields;

    /**
     * @param array|null $blacklistedFields
     */
    public function __construct(?array $blacklistedFields = null)
    {
        $this->blacklistedFields = (array) $blacklistedFields;
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
        foreach ($this->blacklistedFields as $blacklistedField) {
            if (isset($type->$blacklistedField)) {
                unset($type->$blacklistedField);
            }
        }

        return $type;
    }
}

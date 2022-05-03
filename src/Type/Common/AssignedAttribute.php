<?php

namespace Makaira\OxidConnectEssential\Type\Common;

use Kore\DataObject\DataObject;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AssignedAttribute extends DataObject
{
    public ?string $oxid = null;
    public ?string $oxtitle = null;
    public ?string $oxvalue = null;
}

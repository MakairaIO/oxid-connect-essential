<?php

namespace Makaira\OxidConnectEssential\Type\Common;

use Kore\DataObject\DataObject;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AssignedAttribute extends DataObject
{
    public ?string $oxid;
    public ?string $oxtitle;
    public ?string $oxvalue;
}

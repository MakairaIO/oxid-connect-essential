<?php

namespace Makaira\OxidConnectEssential\Type\Common;

use Kore\DataObject\DataObject;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AssignedTypedAttribute extends DataObject
{
    public $id;
    public $title;
    public $value;

    public function __toString()
    {
        return md5($this->id . $this->value);
    }
}

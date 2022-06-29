<?php

namespace Makaira\OxidConnectEssential\Type\Common;

use Kore\DataObject\DataObject;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class AssignedTypedAttribute extends DataObject
{
    public string $id;
    public string $title;

    /**
     * @var string|int|float
     */
    public $value;

    public function __toString()
    {
        return md5($this->id . $this->value);
    }
}

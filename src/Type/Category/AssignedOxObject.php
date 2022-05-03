<?php

namespace Makaira\OxidConnectEssential\Type\Category;

use Kore\DataObject\DataObject;

class AssignedOxObject extends DataObject
{
    public ?string $oxid = null;
    public ?int $oxpos = null;
}

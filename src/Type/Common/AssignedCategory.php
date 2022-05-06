<?php

namespace Makaira\OxidConnectEssential\Type\Common;

use Kore\DataObject\DataObject;

class AssignedCategory extends DataObject
{
    public ?string $catid = null;
    public ?string $title = null;
    public ?int $shopid = null;
    public ?int $pos = null;
    public ?string $path = null;
}

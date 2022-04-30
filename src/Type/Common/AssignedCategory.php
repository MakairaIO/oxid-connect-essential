<?php

namespace Makaira\OxidConnectEssential\Type\Common;

use Kore\DataObject\DataObject;

class AssignedCategory extends DataObject
{
    public ?string $catid;
    public ?string $title;
    public ?int $shopid;
    public ?int $pos;
    public ?string $path;
}

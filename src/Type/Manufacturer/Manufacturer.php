<?php

namespace Makaira\OxidConnectEssential\Type\Manufacturer;

use Makaira\OxidConnectEssential\Type;

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */
class Manufacturer extends Type
{
    public $manufacturer_title;
    public $shortdesc;
    public $selfLinks = [];
    public $meta_keywords;
    public $meta_description;
}

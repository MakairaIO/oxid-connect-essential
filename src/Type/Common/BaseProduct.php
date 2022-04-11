<?php

namespace Makaira\OxidConnectEssential\Type\Common;

use Makaira\OxidConnectEssential\Type;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class BaseProduct extends Type
{
    /* attributes as String */
    public $attributeStr = [];

    /* attributes as Integer */
    public $attributeInt = [];

    /* attributes as Float */
    public $attributeFloat = [];

    /* required fields + mak-fields */
    public $isPseudo = false;
    public $ean = '';
    public $title = '';
    public $searchkeys = '';
    public $hidden = false;
    public $sort = 0;
    public $longdesc = '';
    public $shortdesc = '';
    public $stock = 0;
    public $onstock = false;
    public $manufacturerid = '';
    public $manufacturer_title = '';
    public $price = 0.00;
    public $insert;
    public $soldamount = 0;
    public $rating = 0.0;
    public $searchable = true;
    public $picture_url_main = [];
}

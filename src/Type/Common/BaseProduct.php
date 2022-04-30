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
    public array $attributeStr = [];

    /* attributes as Integer */
    public array $attributeInt = [];

    /* attributes as Float */
    public array $attributeFloat = [];

    /* required fields + mak-fields */
    public bool $isPseudo = false;
    public string $ean = '';
    public string $title = '';
    public string $searchkeys = '';
    public bool $hidden = false;
    public int $sort = 0;
    public string $longdesc = '';
    public string $shortdesc = '';
    public int $stock = 0;
    public bool $onstock = false;
    public string $manufacturerid = '';
    public string $manufacturer_title = '';
    public float $price = 0.00;
    public ?string $insert;
    public int $soldamount = 0;
    public float $rating = 0.0;
    public bool $searchable = true;
    public array $picture_url_main = [];
}

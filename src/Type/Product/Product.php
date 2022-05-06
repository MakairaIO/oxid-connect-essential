<?php

namespace Makaira\OxidConnectEssential\Type\Product;

use Makaira\OxidConnectEssential\Type;

/**
 * @SuppressWarnings(TooManyFields)
 */
class Product extends Type
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
    public int $stock = 1;
    public bool $onstock = true;
    public string $manufacturerid = '';
    public string $manufacturer_title = '';
    public float $price = 0.00;
    public ?string $insert = null;
    public int $soldamount = 0;
    public float $rating = 0.0;
    public bool $searchable = true;
    public array $picture_url_main = [];

    /* variant attributes */
    /** @var array<int|string, array<string, float|int|string>|float|int|string>  */
    public array $attributes = [];

    public string $parent = '';

    public ?string $maincategory = null;
    public ?string $maincategoryurl = null;

    public float $mak_boost_norm_insert = 0.0;
    public float $mak_boost_norm_sold = 0.0;
    public float $mak_boost_norm_rating = 0.0;
    public float $mak_boost_norm_revenue = 0.0;
    public float $mak_boost_norm_profit_margin = 0.0;

    public bool $isVariant = false;
    public ?string $activeto = null;
    public ?string $activefrom = null;
    public array $suggest = [];
    public array $category = [];
    public ?string $TRACKING = null;

    public array $tmpAttributeStr = [];
    public array $tmpAttributeInt = [];
    public array $tmpAttributeFloat = [];
}

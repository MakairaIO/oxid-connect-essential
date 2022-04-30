<?php

namespace Makaira\OxidConnectEssential\Type\Product;

use Makaira\OxidConnectEssential\Type\Common\BaseProduct;

/**
 * @SuppressWarnings(TooManyFields)
 */
class Product extends BaseProduct
{
    /* variant attributes */
    /** @var array<int|string, array<string, float|int|string>|float|int|string>  */
    public array $attributes = [];

    public string $parent = '';

    public ?string $meta_keywords;
    public ?string $meta_description;
    public ?string $maincategory;
    public ?string $maincategoryurl;

    public float $mak_boost_norm_insert = 0.0;
    public float $mak_boost_norm_sold = 0.0;
    public float $mak_boost_norm_rating = 0.0;
    public float $mak_boost_norm_revenue = 0.0;
    public float $mak_boost_norm_profit_margin = 0.0;

    public bool $isVariant = false;
    public ?string $activeto;
    public ?string $activefrom;
    public array $suggest = [];
    public array $category = [];
    public ?string $TRACKING;

    public array $tmpAttributeStr = [];
    public array $tmpAttributeInt = [];
    public array $tmpAttributeFloat = [];
}

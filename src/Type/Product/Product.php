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

    public ?string $meta_keywords = null;
    public ?string $meta_description = null;
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

<?php

namespace Makaira\OxidConnectEssential\Type\Product;

use Makaira\OxidConnectEssential\Type\Common\BaseProduct;

/**
 * @SuppressWarnings(TooManyFields)
 */
class Product extends BaseProduct
{
    /* variant attributes */
    public $attributes = [];

    public $parent = '';

    public $meta_keywords;
    public $meta_description;
    public $selfLinks = [];
    public $maincategory;
    public $maincategoryurl;

    public $mak_boost_norm_insert = 0.0;
    public $mak_boost_norm_sold = 0.0;
    public $mak_boost_norm_rating = 0.0;
    public $mak_boost_norm_revenue = 0.0;
    public $mak_boost_norm_profit_margin = 0.0;

    public $isVariant = false;
    public $activeto;
    public $activefrom;
    public $suggest = [];
    public $category = [];
    public $TRACKING;

    public $tmpAttributeStr = [];
    public $tmpAttributeInt = [];
    public $tmpAttributeFloat = [];
}

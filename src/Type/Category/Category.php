<?php

namespace Makaira\OxidConnectEssential\Type\Category;

use Makaira\OxidConnectEssential\Type;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Category extends Type
{
    public ?string $category_title;
    public ?string $sort;
    public ?string $shortdesc;
    public ?string $longdesc;
    public ?string $meta_keywords;
    public ?string $meta_description;
    public ?string $hierarchy;
    public int $depth;
    public array $subcategories = [];
    public bool $hidden = false;
    public array $oxobject = [];
}

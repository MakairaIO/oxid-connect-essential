<?php

namespace Makaira\OxidConnectEssential\Type\Category;

use Makaira\OxidConnectEssential\Type;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Category extends Type
{
    public ?string $category_title = null;
    public ?string $sort = null;
    public ?string $shortdesc = null;
    public ?string $longdesc = null;
    public ?string $meta_keywords = null;
    public ?string $meta_description = null;
    public ?string $hierarchy = null;
    public int $depth = 0;
    public array $subcategories = [];
    public bool $hidden = false;
    public array $oxobject = [];
}

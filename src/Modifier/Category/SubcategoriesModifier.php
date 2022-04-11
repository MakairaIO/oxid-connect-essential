<?php

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\CategoryInheritance;

class SubcategoriesModifier extends Modifier
{
    public function __construct(private CategoryInheritance $categoryInheritance)
    {
    }

    public function apply(Type $category)
    {
        $category->subcategories = $this->categoryInheritance->buildCategoryInheritance($category->id);

        return $category;
    }
}

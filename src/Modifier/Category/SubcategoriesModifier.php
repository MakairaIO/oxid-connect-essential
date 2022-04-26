<?php

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\CategoryInheritance;

class SubcategoriesModifier extends Modifier
{
    private CategoryInheritance $categoryInheritance;

    public function __construct(CategoryInheritance $categoryInheritance)
    {
        $this->categoryInheritance = $categoryInheritance;
    }

    public function apply(Type $category)
    {
        $category->subcategories = $this->categoryInheritance->buildCategoryInheritance($category->id);

        return $category;
    }
}

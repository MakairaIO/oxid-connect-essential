<?php

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\CategoryInheritance;
use Makaira\OxidConnectEssential\Type\Category\Category;

class SubcategoriesModifier extends Modifier
{
    private CategoryInheritance $categoryInheritance;

    public function __construct(CategoryInheritance $categoryInheritance)
    {
        $this->categoryInheritance = $categoryInheritance;
    }

    /**
     * @param Category $category
     *
     * @return Category
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function apply(Type $category): Category
    {
        $category->subcategories = $this->categoryInheritance->buildCategoryInheritance($category->id);

        return $category;
    }
}

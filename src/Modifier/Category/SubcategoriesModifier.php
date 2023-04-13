<?php

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\CategoryInheritance;
use Makaira\OxidConnectEssential\Type\Category\Category;

class SubcategoriesModifier extends Modifier
{
    public function __construct(private CategoryInheritance $categoryInheritance)
    {
    }

    /**
     * @param Category $category
     *
     * @return Category
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $category): Category
    {
        if (null !== $category->id) {
            $category->subcategories = $this->categoryInheritance->buildCategoryInheritance($category->id);
        }

        return $category;
    }
}

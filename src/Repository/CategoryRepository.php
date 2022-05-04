<?php

namespace Makaira\OxidConnectEssential\Repository;

use Makaira\OxidConnectEssential\Type\Category\Category;

class CategoryRepository extends AbstractRepository
{
    /**
     * Get TYPE of repository.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'category';
    }

    /**
     * Get an instance of current type.
     *
     * @param string $id
     *
     * @return Category
     */
    public function getInstance(string $id): Category
    {
        return new Category(['id' => $id]);
    }

    protected function getSelectQuery(): string
    {
        return "
          SELECT
            oxcategories.OXID as `id`,
            oxcategories.oxtimestamp AS `timestamp`,
            oxcategories.*
          FROM
            oxcategories
          WHERE
            oxcategories.oxid = :id
        ";
    }

    protected function getAllIdsQuery(): string
    {
        return "SELECT OXID FROM oxcategories ORDER BY OXID";
    }

    protected function getParentIdQuery(): string
    {
        return "SELECT OXPARENTID FROM oxcategories WHERE oxcategories.oxid = :id";
    }
}

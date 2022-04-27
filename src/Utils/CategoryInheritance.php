<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Utils;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;

class CategoryInheritance
{
    private Connection $database;

    private bool $useCategoryInheritance;

    /**
     * CategoryInheritance constructor.
     *
     * @param Connection $database
     * @param bool       $useCategoryInheritance
     */
    public function __construct(
        Connection $database,
        bool $useCategoryInheritance
    ) {
        $this->useCategoryInheritance = $useCategoryInheritance;
        $this->database               = $database;
    }

    /**
     * @param string $categoryId
     *
     * @return array|string
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function buildCategoryInheritance(string $categoryId)
    {
        if (!isset($categoryId) || !$this->useCategoryInheritance) {
            return $categoryId;
        }

        $category = oxNew('oxcategory');
        if ($category->load($categoryId)) {
            $result     = $this->database->executeQuery(
                "SELECT OXID FROM oxcategories WHERE OXROOTID = :rootId AND OXLEFT > :left AND OXRIGHT < :right",
                [
                    'rootId' => $category->oxcategories__oxrootid->value,
                    'left' => $category->oxcategories__oxleft->value,
                    'right' => $category->oxcategories__oxright->value
                ]
            );
            $categoryId = array_merge(
                (array) $categoryId,
                $result->fetchFirstColumn()
            );
        }

        return $categoryId;
    }
}

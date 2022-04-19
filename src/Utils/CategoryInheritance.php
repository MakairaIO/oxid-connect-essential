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

class CategoryInheritance
{
    /**
     * CategoryInheritance constructor.
     *
     * @param Connection $database
     * @param bool       $useCategoryInheritance
     */
    public function __construct(
        private Connection $database,
        private bool $useCategoryInheritance
    ) {
    }

    /**
     * @param $categoryId
     *
     * @return array
     */
    public function buildCategoryInheritance($categoryId)
    {
        if (!isset($categoryId) || !$this->useCategoryInheritance) {
            return $categoryId;
        }

        $oCategory = oxNew('oxcategory');
        $oCategory->load($categoryId);
        if ($oCategory) {
            $result     = $this->database->getColumn(
                "SELECT OXID FROM oxcategories WHERE OXROOTID = :rootId AND OXLEFT > :left AND OXRIGHT < :right",
                [
                    'rootId' => $oCategory->oxcategories__oxrootid->value,
                    'left' => $oCategory->oxcategories__oxleft->value,
                    'right' => $oCategory->oxcategories__oxright->value
                ]
            );
            $categoryId = array_merge(
                (array) $categoryId,
                $result
            );
        }

        return $categoryId;
    }
}

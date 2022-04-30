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
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use OxidEsales\Eshop\Application\Model\Category;

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
     * @return array<string>
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function buildCategoryInheritance(string $categoryId): array
    {
        $categories = [$categoryId];

        if ($this->useCategoryInheritance) {
            $category = oxNew(Category::class);
            if ($category->load($categoryId)) {
                $sql = "SELECT `OXID` FROM `oxcategories`
                    WHERE `OXROOTID` = :rootId AND `OXLEFT` >= :left AND `OXRIGHT` <= :right
                    ORDER BY `OXLEFT` ASC";

                /** @var Result $result */
                $result = $this->database->executeQuery(
                    $sql,
                    [
                        'rootId' => $category->getFieldData('oxrootid'),
                        'left'   => $category->getFieldData('oxleft'),
                        'right'  => $category->getFieldData('oxright'),
                    ]
                );

                /** @var array<string> $categories */
                $categories = $result->fetchFirstColumn();
            }
        }

        return $categories;
    }
}

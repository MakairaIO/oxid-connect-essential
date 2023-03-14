<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Category\Category;
use Makaira\OxidConnectEssential\Utils\TableTranslator;

class HierarchyModifier extends Modifier
{
    protected string $selectQuery = "
      SELECT
        oc.OXID
      FROM
        oxcategories oc
      WHERE
        oc.OXLEFT <= :left 
        AND oc.OXRIGHT >= :right 
        AND oc.OXROOTID = :rootId
      ORDER BY oc.OXLEFT;
    ";

    private Connection $database;

    private TableTranslator $tableTranslator;

    public function __construct(Connection $database, TableTranslator $tableTranslator)
    {
        $this->database        = $database;
        $this->tableTranslator = $tableTranslator;
    }

    /**
     * Modify product and return modified product
     *
     * @param Category $category
     *
     * @return Category
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $category): Category
    {
        /** @var Result $resultStatement */
        $resultStatement = $this->database->executeQuery(
            $this->tableTranslator->translate($this->selectQuery),
            [
                'left'   => $category->additionalData['OXLEFT'],
                'right'  => $category->additionalData['OXRIGHT'],
                'rootId' => $category->additionalData['OXROOTID'],
            ]
        );

        $hierarchy = $resultStatement->fetchFirstColumn();

        $category->depth     = count($hierarchy);
        $category->hierarchy = implode('//', $hierarchy);

        return $category;
    }
}

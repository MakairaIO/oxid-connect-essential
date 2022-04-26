<?php

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Common\AssignedCategory;
use Makaira\OxidConnectEssential\Type\Common\BaseProduct;

class CategoryModifier extends Modifier
{
    private string $selectCategoriesQuery = "
        SELECT
            o2c.oxcatnid AS catid,
            o2c.oxpos AS oxpos,
            o2c.oxshopid AS shopid,
            oc.OXTITLE as title,
            oc.OXACTIVE AS active,
            oc.OXLEFT AS oxleft,
            oc.OXRIGHT AS oxright,
            oc.OXROOTID AS oxrootid
        FROM
            oxobject2category o2c
        LEFT JOIN oxcategories oc ON
            o2c.oxcatnid = oc.oxid
        WHERE
            o2c.oxobjectid = :productId
            AND :productActive = :productActive
        ";

    protected string $selectCategoryPathQuery = "
      SELECT
        oc.OXTITLE as title,
        oc.OXACTIVE as active
      FROM
        oxcategories oc
      WHERE
        oc.OXLEFT <= :left
        AND oc.OXRIGHT >= :right
        AND oc.OXROOTID = :rootId
      ORDER BY oc.OXLEFT;
    ";

    private Connection $database;

    /**
     * @param Connection $database
     */
    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    /**
     * Modify product and return modified product
     *
     * @param BaseProduct $product
     *
     * @return BaseProduct
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $product)
    {
        $allCats = $this->database
            ->executeQuery(
                $this->selectCategoriesQuery,
                [
                    'productId'     => $product->id,
                    'productActive' => $product->active,
                ]
            )
            ->fetchAllAssociative();

        $categories = [];

        foreach ($allCats as $cat) {
            $catPaths = $this->database
                ->executeQuery(
                    $this->selectCategoryPathQuery,
                    [
                        'left'      => $cat['oxleft'],
                        'right'     => $cat['oxright'],
                        'rootId'    => $cat['oxrootid'],
                    ]
                )
                ->fetchAllAssociative();

            $path  = '';
            $active = true;
            foreach ($catPaths as $catPath) {
                $active &= $catPath['active'];
                if (!$active) {
                    break;
                }
                $path .= $catPath['title'] . '/';
            }

            if ($active) {
                $categories[] = new AssignedCategory(
                    [
                        'catid'  => $cat['catid'],
                        'title'  => $cat['title'],
                        'shopid' => $cat['shopid'],
                        'pos'    => $cat['oxpos'],
                        'path'   => $path,
                    ]
                );
            }
        }

        $product->category = $categories;

        return $product;
    }
}

<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\SeoEncoderCategory;
use OxidEsales\Eshop\Core\Language;

class MainCategoryModifier extends Modifier
{
    private Connection $database;

    private SeoEncoderCategory $encoder;

    private Language $oxLang;

    /**
     * @param Connection         $database
     * @param SeoEncoderCategory $encoder
     * @param Language           $oxLang
     */
    public function __construct(
        Connection $database,
        SeoEncoderCategory $encoder,
        Language $oxLang
    ) {
        $this->oxLang   = $oxLang;
        $this->encoder  = $encoder;
        $this->database = $database;
    }

    /**
     * Modify product and return modified product
     *
     * @param Type\Product\Product $type
     *
     * @return Type\Product\Product
     * @throws DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function apply(Type $type)
    {
        // skip database request if field is already set
        if (!isset($type->maincategory)) {
            $sql = "SELECT `OXCATNID` FROM `oxobject2category` WHERE `OXOBJECTID`= ? ORDER BY `OXTIME` LIMIT 1";

            /** @var Result $resultStatement */
            $resultStatement = $this->database->executeQuery($sql, [$type->parent ?: $type->id]);

            /** @var string $categoryId */
            $categoryId = $resultStatement->fetchOne();

            if ($categoryId) {
                $type->maincategory = $categoryId;
                $languageId         = (int) $this->oxLang->getBaseLanguage();
                $oCategory          = oxNew(Category::class);
                $oCategory->loadInLang($languageId, $categoryId);
                $type->maincategoryurl = $this->encoder->getCategoryUri($oCategory, $languageId);
            }
        }

        return $type;
    }
}

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
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\Eshop\Application\Model\SeoEncoderCategory;
use OxidEsales\Eshop\Core\Language;

class MainCategoryModifier extends Modifier
{
    /**
     * @param Connection         $database
     * @param SeoEncoderCategory $encoder
     * @param Language           $oxLang
     */
    public function __construct(
        private Connection $database,
        private SeoEncoderCategory $encoder,
        private Language $oxLang
    ) {
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     * @throws DBALException
     */
    public function apply(Type $type)
    {
        // skip database request if field is already set
        if (!isset($type->maincategory)) {
            $sql = "SELECT `OXCATNID` FROM `oxobject2category` WHERE `OXOBJECTID`= ? ORDER BY `OXTIME` LIMIT 1";

            $categoryId = (string) $this->database->executeQuery($sql, [$type->OXPARENTID ?: $type->OXID])->fetchOne();

            if ($categoryId) {
                $type->maincategory = $categoryId;
                $languageId         = $this->oxLang->getBaseLanguage();
                $oCategory          = oxNew('oxcategory');
                $oCategory->loadInLang($languageId, $categoryId);
                $type->maincategoryurl = $this->encoder->getCategoryUri($oCategory, $languageId);
            }
        }

        return $type;
    }
}

<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Model\BaseModel;

use function array_keys;

abstract class AbstractUrlModifier extends Modifier
{
    /**
     * UrlModifier constructor.
     *
     * @param Language $language
     */
    public function __construct(private Language $language)
    {
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     */
    public function apply(Type $type): Type
    {
        $objectData = array_merge((array) $type, $type->additionalData);
        $objectData['oxid'] = $objectData['id'];

        //@formatter:off
        unset(
            $objectData['active'],
            $objectData['activefrom'],
            $objectData['activeto'],
            $objectData['additionalData'],
            $objectData['attribute'],
            $objectData['category'],
            $objectData['id'],
            $objectData['active'],
            $objectData['shop'],
            $objectData['suggest'],
            $objectData['timestamp'],
            $objectData['url'],
            $objectData['variantactive']
        );
        //@formatter:on

        $object = $this->createModelInstance();
        $object->assign($objectData);

        $type->url = $this->getUrl($type, $object, (int) $this->language->getBaseLanguage());

        $type->selfLinks = [];
        /** @var array<int> $languageIds */
        $languageIds     = $this->language->getLanguageIds();
        foreach (array_keys($languageIds) as $id) {
            $key                   = $languageIds[$id];
            $type->selfLinks[$key] = $this->getUrl($type, $object, $id);
        }

        return $type;
    }

    abstract protected function createModelInstance(): BaseModel;

    abstract protected function getUrl(Type $type, BaseModel $model, int $languageId): string;
}

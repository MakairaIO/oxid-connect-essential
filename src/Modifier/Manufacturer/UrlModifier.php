<?php

namespace Makaira\OxidConnectEssential\Modifier\Manufacturer;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractUrlModifier;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Application\Model\SeoEncoderManufacturer;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Model\BaseModel;

class UrlModifier extends AbstractUrlModifier
{
    /**
     * @param SeoEncoderManufacturer $seoEncoderManufacturer
     * @param Language               $language
     */
    public function __construct(private SeoEncoderManufacturer $seoEncoderManufacturer, Language $language)
    {
        parent::__construct($language);
    }

    /**
     * @return BaseModel
     */
    protected function createModelInstance(): BaseModel
    {
        return new Manufacturer();
    }

    /**
     * @param Type      $type
     * @param BaseModel $model
     * @param int       $languageId
     *
     * @return string
     */
    protected function getUrl(Type $type, BaseModel $model, int $languageId): string
    {
        $url = '';

        if ($model instanceof Manufacturer) {
            $url = $this->seoEncoderManufacturer->getManufacturerUri($model, $languageId);
        }

        return $url;
    }
}

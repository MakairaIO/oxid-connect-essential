<?php

namespace Makaira\OxidConnectEssential\Modifier\Category;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractUrlModifier;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\SeoEncoderCategory;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Model\BaseModel;

class UrlModifier extends AbstractUrlModifier
{
    private SeoEncoderCategory $encoder;

    /**
     * @param SeoEncoderCategory $encoder
     * @param Language           $oxLang
     */
    public function __construct(SeoEncoderCategory $encoder, Language $oxLang)
    {
        $this->encoder = $encoder;
        parent::__construct($oxLang);
    }

    /**
     * @return BaseModel
     */
    protected function createModelInstance(): BaseModel
    {
        return new Category();
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

        if ($model instanceof Category) {
            $url = $this->encoder->getCategoryUri($model, $languageId);
        }

        return $url;
    }
}

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
    /**
     * @param SeoEncoderCategory $seoEncoderCategory
     * @param Language           $language
     */
    public function __construct(private SeoEncoderCategory $seoEncoderCategory, Language $language)
    {
        parent::__construct($language);
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
            $url = $this->seoEncoderCategory->getCategoryUri($model, $languageId);
        }

        return $url;
    }
}

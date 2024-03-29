<?php

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Makaira\OxidConnectEssential\Modifier\Common\AbstractUrlModifier;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\SeoEncoderArticle;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Model\BaseModel;

class UrlModifier extends AbstractUrlModifier
{
    /**
     * @param SeoEncoderArticle $seoEncoderArticle
     * @param Language          $language
     */
    public function __construct(private SeoEncoderArticle $seoEncoderArticle, Language $language)
    {
        parent::__construct($language);
    }

    /**
     * @return BaseModel
     */
    protected function createModelInstance(): BaseModel
    {
        return new Article();
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

        if ($model instanceof Article) {
            if (!isset($type->additionalData['picture_url_main'])) {
                $type->additionalData['picture_url_main'] = $model->getMasterZoomPictureUrl(1);
            }

            $url = $this->seoEncoderArticle->getArticleMainUri($model, $languageId);
        }

        return $url;
    }
}

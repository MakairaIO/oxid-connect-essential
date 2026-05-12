<?php

namespace Makaira\OxidConnectEssential\RevisionHandler\Extractor;

use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\AbstractModelDataExtractor;
use OxidEsales\Eshop\Application\Model\Article as ArticleModel;
use OxidEsales\Eshop\Core\Model\BaseModel;

class Article extends AbstractModelDataExtractor
{
    /**
     * @param ArticleModel $model
     *
     * @return array<Revision>
     */
    public function extract(BaseModel $model): array
    {
        $isParentArticle = !$model->getParentId();
        $revisionInput = [];
        if ($isParentArticle) {
            $revisionInput[$model->getId()] = Revision::TYPE_PRODUCT;
            foreach ($model->getVariantIds(false) as $variantId) {
                $revisionInput[$variantId] = Revision::TYPE_VARIANT;
            }
        } else {
            $revisionInput[$model->getParentId()] = Revision::TYPE_PRODUCT;
            $revisionInput[$model->getId()] = Revision::TYPE_VARIANT;
        }

        return $this->buildRevisions($revisionInput);
    }

    /**
     * @param BaseModel $model
     *
     * @return bool
     */
    public function supports(BaseModel $model): bool
    {
        return $model instanceof ArticleModel && null !== $model->getId();
    }
}

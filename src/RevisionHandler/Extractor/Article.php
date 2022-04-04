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
        return $this->buildRevistion(
            $model->getParentId() ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT,
            $model->getId()
        );
    }

    /**
     * @param BaseModel $model
     *
     * @return bool
     */
    public function supports(BaseModel $model): bool
    {
        return $model instanceof ArticleModel;
    }
}

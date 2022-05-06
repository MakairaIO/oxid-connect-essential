<?php

namespace Makaira\OxidConnectEssential\RevisionHandler;

use Makaira\OxidConnectEssential\Domain\Revision;
use OxidEsales\Eshop\Core\Model\BaseModel;

interface ModelDataExtractorInterface
{
    /**
     * @param BaseModel $model
     *
     * @return array<string, Revision>
     */
    public function extract(BaseModel $model): array;

    /**
     * @param BaseModel $model
     *
     * @return bool
     */
    public function supports(BaseModel $model): bool;
}

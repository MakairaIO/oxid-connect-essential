<?php

declare(strict_types=1);

namespace Makaira\OxidConnectEssential\RevisionHandler;

use Makaira\OxidConnectEssential\Domain\Revision;
use OxidEsales\Eshop\Core\Model\BaseModel;

use function get_class;

class ModelDataExtractor
{
    /**
     * @param iterable<AbstractModelDataExtractor> $extractors
     */
    public function __construct(private iterable $extractors)
    {
    }

    /**
     * @param BaseModel $model
     *
     * @return array<Revision>
     * @throws ModelNotSupportedException
     */
    public function extractData(BaseModel $model): array
    {
        $dataExtractor = null;
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($model)) {
                $dataExtractor = $extractor;
                break;
            }
        }

        if (!($dataExtractor instanceof AbstractModelDataExtractor)) {
            throw new ModelNotSupportedException(sprintf("The model '%s' is not supported.", get_class($model)));
        }

        return $dataExtractor->extract($model);
    }
}

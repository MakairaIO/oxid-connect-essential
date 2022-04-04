<?php

namespace Makaira\OxidConnectEssential\RevisionHandler;

use DateTimeInterface;
use Makaira\OxidConnectEssential\Domain\Revision;

abstract class AbstractModelDataExtractor implements ModelDataExtractorInterface
{
    /**
     * @param string                 $type
     * @param string                 $objectId
     * @param DateTimeInterface|null $changed
     * @param int|null               $revision
     *
     * @return array<Revision>
     */
    protected function buildRevistion(
        string $type,
        string $objectId,
        ?DateTimeInterface $changed = null,
        ?int $revision = null
    ) {
        $key = sprintf('%s-%s', $type, $objectId);

        return [$key => new Revision($type, $objectId, $changed, $revision)];
    }
}

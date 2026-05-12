<?php

namespace Makaira\OxidConnectEssential\RevisionHandler;

use DateTimeInterface;
use Makaira\OxidConnectEssential\Domain\Revision;

abstract class AbstractModelDataExtractor implements ModelDataExtractorInterface
{
    /**
     * @param string                 $type
     * @param string                 $objectId
     *
     * @return array<Revision>
     */
    protected function buildRevision(
        string $type,
        string $objectId,
    ): array {
        return $this->buildRevisions([$objectId => $type]);
    }

    /**
     * @param array<string, string>  $input
     *
     * @return array<Revision>
     */
    protected function buildRevisions(
        array $input,
    ): array {
        $revisions = [];
        foreach ($input as $objectId => $type) {
            $key = sprintf('%s-%s', $type, $objectId);
            $revisions[$key] = new Revision($type, $objectId);
        }

        return $revisions;
    }
}

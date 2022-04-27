<?php

namespace Makaira\OxidConnectEssential\Rpc\Handler;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\Rpc\HandlerInterface;

class GetReplicationStatus implements HandlerInterface
{
    private RevisionRepository $repository;

    /**
     * @param RevisionRepository $repository
     */
    public function __construct(RevisionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $request
     *
     * @return array
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function handle(array $request): array
    {
        $indices = $request['indices'] ?? [];

        foreach ($request['indices'] as &$index) {
            $index['openChanges'] = $this->repository->countChanges($index['lastRevision']);
        }
        unset($index);

        return $indices;
    }
}

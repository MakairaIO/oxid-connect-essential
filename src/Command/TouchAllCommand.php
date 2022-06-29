<?php

namespace Makaira\OxidConnectEssential\Command;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\Repository\AbstractRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_map;
use function count;
use function sprintf;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TouchAllCommand extends Command
{
    /**
     * @var iterable<AbstractRepository>
     */
    private iterable $repositories;

    private RevisionRepository $revisionRepository;

    /**
     * @param iterable<AbstractRepository> $repositories
     * @param RevisionRepository           $revisionRepository
     */
    public function __construct(iterable $repositories, RevisionRepository $revisionRepository)
    {
        $this->revisionRepository = $revisionRepository;
        $this->repositories       = $repositories;
        parent::__construct('makaira:touch-all');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws DBALDriverException
     * @throws DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->repositories as $repository) {
            $output->write(sprintf('Touching <fg=green>%s</>', $repository->getType()));
            $revisions     = array_map(
                static fn($objectId) => new Revision($repository->getType(), $objectId),
                $repository->getAllIds()
            );
            $revisionCount = count($revisions);
            $output->write(" <fg=yellow>{$revisionCount} items</> ...");

            $this->revisionRepository->storeRevisions($revisions);

            $output->writeln(' done');
        }

        return 0;
    }
}

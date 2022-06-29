<?php

namespace Makaira\OxidConnectEssential\Command;

use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CleanUpCommand extends Command
{
    private RevisionRepository $revisionRepository;

    public function __construct(RevisionRepository $revisionRepository)
    {
        $this->revisionRepository = $revisionRepository;
        parent::__construct('makaira:cleanup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Removing registered revisions...');
        $this->revisionRepository->cleanup();
        $output->writeln('done');

        return 0;
    }
}

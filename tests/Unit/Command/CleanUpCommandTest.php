<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Command;

use Makaira\OxidConnectEssential\Command\CleanUpCommand;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CleanUpCommandTest extends TestCase
{
    public function testCleanUpIsCalled(): void
    {
        $repositoryMock = $this->createMock(RevisionRepository::class);
        $repositoryMock->expects($this->once())->method('cleanup');

        $input   = new ArrayInput([]);
        $output  = new BufferedOutput();
        $command = new CleanUpCommand($repositoryMock);
        $command->run($input, $output);

        $this->assertSame("Removing registered revisions...done\n", $output->fetch());
    }
}

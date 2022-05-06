<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Command;

use Symfony\Component\Console\Output\BufferedOutput;
use Makaira\OxidConnectEssential\Command\TouchAllCommand;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\Repository\CategoryRepository;
use Makaira\OxidConnectEssential\Repository\ManufacturerRepository;
use Makaira\OxidConnectEssential\Repository\ProductRepository;
use OxidEsales\TestingLibrary\UnitTestCase;
use Symfony\Component\Console\Input\ArrayInput;

class TouchAllCommandTest extends UnitTestCase
{
    public function testCreatesRevisions()
    {
        $productRepository = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethodsExcept(['getType'])
            ->getMock();
        $productRepository->method('getAllIds')
            ->willReturn(['product_21', 'product_42', 'product_84'])
            ->withAnyParameters();

        $categoryRepository = $this->getMockBuilder(CategoryRepository::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethodsExcept(['getType'])
            ->getMock();
        $categoryRepository->method('getAllIds')
            ->willReturn(['category_21', 'category_42', 'category_84'])
            ->withAnyParameters();

        $manufacturerRepository = $this->getMockBuilder(ManufacturerRepository::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethodsExcept(['getType'])
            ->getMock();
        $manufacturerRepository->method('getAllIds')
            ->willReturn(['manufacturer_21', 'manufacturer_42', 'manufacturer_84'])
            ->withAnyParameters();

        $revisionRepository = $this->createMock(RevisionRepository::class);
        $revisionRepository->expects($this->exactly(3))
            ->method('storeRevisions')
            ->withAnyParameters();

        $command = new TouchAllCommand(
            [$productRepository, $categoryRepository, $manufacturerRepository],
            $revisionRepository
        );

        $input  = new ArrayInput([]);
        $output = new BufferedOutput();

        $command->run($input, $output);

        $expected = <<<'EOT'
Touching product 3 items ... done
Touching category 3 items ... done
Touching manufacturer 3 items ... done

EOT;

        $this->assertSame($expected, $output->fetch());
    }
}

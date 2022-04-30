<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Change;
use Makaira\OxidConnectEssential\Repository\AbstractRepository;
use Makaira\OxidConnectEssential\Repository\ManufacturerRepository;
use Makaira\OxidConnectEssential\Repository\ModifierList;
use Makaira\OxidConnectEssential\Test\TableTranslatorTrait;
use Makaira\OxidConnectEssential\Type\Manufacturer\Manufacturer;
use OxidEsales\TestingLibrary\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ManufacturerRepositoryTest extends UnitTestCase
{
    use TableTranslatorTrait;

    public function testLoadManufacturer()
    {
        /**
         * @var MockObject<ModifierList> $modifiersMock
         * @var AbstractRepository $repository
         */
        [$modifiersMock, $repository] = $this->createRepository(['id' => 42]);

        $modifiersMock
            ->method('applyModifiers')
            ->willReturnArgument(0);

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(
                [
                    'id' => 42,
                    'type' => 'manufacturer',
                    'data' => new Manufacturer(
                        [
                            'id' => 42,
                        ]
                    ),
                ]
            ),
            $change
        );
    }

    public function testSetDeletedMarker()
    {
        /**
         * @var MockObject<ModifierList> $modifiersMock
         * @var AbstractRepository $repository
         */
        [$modifiersMock, $repository] = $this->createRepository([]);

        $modifiersMock
            ->expects($this->never())
            ->method('applyModifiers');

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(
                [
                    'id' => 42,
                    'type' => 'manufacturer',
                    'deleted' => true,
                ]
            ),
            $change
        );
    }

    public function testRunModifierLoadManufacturer()
    {
        /**
         * @var MockObject<ModifierList> $modifiersMock
         * @var AbstractRepository $repository
         */
        [$modifiersMock, $repository] = $this->createRepository(['id' => 42]);

        $modifiersMock
            ->expects($this->once())
            ->method('applyModifiers')
            ->willReturn('modified');

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(
                [
                    'id' => 42,
                    'type' => 'manufacturer',
                    'data' => 'modified',
                ]
            ),
            $change
        );
    }

    public function testGetAllIds()
    {
        /**
         * @var AbstractRepository $repository
         */
        [, $repository] = $this->createRepository([42], 'fetchFirstColumn');

        $this->assertEquals([42], $repository->getAllIds());
    }

    /**
     * @param array  $dbRow
     * @param string $fetchMethod
     *
     * @return array
     */
    private function createRepository(array $dbRow = [], string $fetchMethod = 'fetchAssociative'): array
    {
        $resultSet = $this->createMock(Result::class);
        $resultSet->method($fetchMethod)->willReturnCallback(static fn() => $dbRow);

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')->willReturn($resultSet);

        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new ManufacturerRepository($databaseMock, $modifiersMock, $this->getTableTranslatorMock());

        return [$modifiersMock, $repository];
    }
}

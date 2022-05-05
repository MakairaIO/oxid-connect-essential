<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Change;
use Makaira\OxidConnectEssential\Repository\AbstractRepository;
use Makaira\OxidConnectEssential\Repository\DataMapper;
use Makaira\OxidConnectEssential\Repository\ModifierList;
use Makaira\OxidConnectEssential\Repository\VariantRepository;
use Makaira\OxidConnectEssential\Test\TableTranslatorTrait;
use Makaira\OxidConnectEssential\Type\Variant\Variant;
use OxidEsales\TestingLibrary\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class VariantRepositoryTest extends UnitTestCase
{
    use TableTranslatorTrait;

    public function testLoadVariant()
    {
        /**
         * @var MockObject<ModifierList> $modifiersMock
         * @var AbstractRepository $repository
         */
        [$modifiersMock, $repository] = $this->createRepository(['id' => 42]);

        $modifiersMock->method('applyModifiers')->willReturnArgument(0);

        $change = $repository->get(42);
        self::assertEquals(
            new Change([
                    'id'   => 42,
                    'type' => 'variant',
                    'data' => new Variant([
                            'id' => 42,
                        ]),
                ]),
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

        $modifiersMock->expects(self::never())->method('applyModifiers');

        $change = $repository->get(42);
        self::assertEquals(
            new Change([
                    'id'      => 42,
                    'type'    => 'variant',
                    'deleted' => true,
                ]),
            $change
        );
    }

    public function testRunModifierLoadVariant()
    {
        /**
         * @var MockObject<ModifierList> $modifiersMock
         * @var AbstractRepository $repository
         */
        [$modifiersMock, $repository] = $this->createRepository(['id' => 42]);

        $type = new Variant(['id' => 42]);
        $modifiersMock->expects(self::once())->method('applyModifiers')->willReturn($type);

        $change = $repository->get(42);
        self::assertEquals(
            new Change([
                    'id'   => 42,
                    'type' => 'variant',
                    'data' => $type,
                ]),
            $change
        );
    }

    public function testGetAllIds()
    {
        /**
         * @var AbstractRepository $repository
         */
        [, $repository] = $this->createRepository([42], 'fetchFirstColumn');

        self::assertEquals([42], $repository->getAllIds());
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
        $resultSet->method($fetchMethod)->willReturn($dbRow);

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')->willReturn($resultSet);

        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new VariantRepository(
            $databaseMock,
            $modifiersMock,
            $this->getTableTranslatorMock(),
            new DataMapper()
        );

        return [$modifiersMock, $repository];
    }
}

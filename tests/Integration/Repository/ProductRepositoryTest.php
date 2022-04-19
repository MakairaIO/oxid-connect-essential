<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Change;
use Makaira\OxidConnectEssential\Repository\AbstractRepository;
use Makaira\OxidConnectEssential\Repository\ModifierList;
use Makaira\OxidConnectEssential\Repository\ProductRepository;
use Makaira\OxidConnectEssential\Test\TableTranslatorTrait;
use Makaira\OxidConnectEssential\Type\Product\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductRepositoryTest extends TestCase
{
    use TableTranslatorTrait;

    public function testLoadProduct(): void
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
            new Change(array(
                'id' => 42,
                'type' => 'product',
                'data' => new Product(array(
                    'id' => 42,
                )),
            )),
            $change
        );
    }

    public function testSetDeletedMarker(): void
    {
        /**
         * @var MockObject<ModifierList> $modifiersMock
         * @var AbstractRepository $repository
         */
        [$modifiersMock, $repository] = $this->createRepository();

        $modifiersMock
            ->expects($this->never())
            ->method('applyModifiers');

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(array(
                'id' => 42,
                'type' => 'product',
                'deleted' => true,
            )),
            $change
        );
    }

    public function testRunModifierLoadProduct(): void
    {
        /**
         * @var MockObject<ModifierList> $modifiersMock
         * @var AbstractRepository $repository
         */
        [$modifiersMock, $repository] = $this->createRepository([['id' => 42]]);

        $modifiersMock
            ->expects($this->once())
            ->method('applyModifiers')
            ->willReturn('modified');

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(array(
                'id' => 42,
                'type' => 'product',
                'data' => 'modified',
            )),
            $change
        );
    }

    public function testGetAllIds(): void
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

        $repository = new ProductRepository($databaseMock, $modifiersMock, $this->getTableTranslatorMock());

        return [$modifiersMock, $repository];
    }
}

<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Utils;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Utils\CategoryInheritance;
use PHPUnit\Framework\TestCase;

class CategoryInheritanceTest extends TestCase
{
    public function testReturnCategoryIdIfInheritanceIsNotUsed()
    {
        $databaseMock        = $this->createMock(Connection::class);
        $categoryInheritance = new CategoryInheritance($databaseMock, false);
        $this->assertSame(['phpunit'], $categoryInheritance->buildCategoryInheritance('phpunit'));
    }

    public function testReturnArrayIfInheritanceIsUsed()
    {
        $ids = ['fad569d6659caca39bc93e98d13dd58b', 'phpunit_21', 'phpunit_42', 'phpunit_84'];

        $resultMock = $this->createMock(Result::class);
        $resultMock->expects($this->once())->method('fetchFirstColumn')->willReturn($ids);

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->expects($this->once())->method('executeQuery')->willReturn($resultMock);

        $categoryInheritance = new CategoryInheritance($databaseMock, true);
        $this->assertSame(
            ['fad569d6659caca39bc93e98d13dd58b', 'phpunit_21', 'phpunit_42', 'phpunit_84'],
            $categoryInheritance->buildCategoryInheritance('fad569d6659caca39bc93e98d13dd58b')
        );
    }
}

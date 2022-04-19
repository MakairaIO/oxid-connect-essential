<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Modifier\Manufacturer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Modifier\Manufacturer\ShopModifier;
use Makaira\OxidConnectEssential\Type\Common\BaseProduct;
use OxidEsales\TestingLibrary\UnitTestCase;

class ShopModifierTest extends UnitTestCase
{
    public function testCEPE()
    {
        $databaseMock = $this->createMock(Connection::class);
        $databaseMock
            ->expects($this->never())
            ->method('executeQuery');

        $product = new BaseProduct();
        $product->OXSHOPID = 'test';
        $modifier = new ShopModifier($databaseMock, false);
        $product = $modifier->apply($product);
        $this->assertEquals(['test'], $product->shop);
    }

    public function testEE()
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchFirstColumn')
            ->willReturn([1, 2]);

        $sql = 'SELECT
          `OXSHOPID`
        FROM
          `oxmanufacturers2shop`
        WHERE
          `OXMAPOBJECTID` = ?';

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')
            ->with($sql, [1])
            ->willReturn($resultMock);

        $product = new BaseProduct();
        $product->OXMAPID = 1;
        $modifier = new ShopModifier($databaseMock, true);
        $product = $modifier->apply($product);
        $this->assertEquals([1, 2], $product->shop);
    }
}

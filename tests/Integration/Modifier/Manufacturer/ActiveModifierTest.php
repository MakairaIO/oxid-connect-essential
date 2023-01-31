<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Modifier\Manufacturer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Modifier\Manufacturer\ActiveModifier;
use Makaira\OxidConnectEssential\Type\Manufacturer\Manufacturer as ManufacturerType;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\Eshop\Core\UtilsObject;
use PHPUnit\Framework\TestCase;

class ActiveModifierTest extends TestCase
{
    public function testModifyTypeIfActive(): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchOne')
            ->willReturn('1');

        $sql = "SELECT COUNT(OXID) > 0
            FROM oxmanufacturers
            WHERE OXID = '42' AND  oxmanufacturers.oxactive = 1 ";

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')
            ->with($sql)
            ->willReturn($resultMock);

        $modelMock = $this->createMock(Manufacturer::class);
        $modelMock->method('getSqlActiveSnippet')
            ->with(true)
            ->willReturn('OXACTIVE = 1');
        $modelMock->method('getCoreTableName')
            ->willReturn('oxcategories_test');

        UtilsObject::setClassInstance(Manufacturer::class, $modelMock);

        $modifier = new ActiveModifier($databaseMock, Manufacturer::class, EshopRegistry::getUtilsObject());
        $type = new ManufacturerType(['id' => 42, 'active' => false]);
        $currentType = $modifier->apply($type);

        static::assertTrue($currentType->active);
    }

    public function testModifyTypeIfInctive(): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchOne')
            ->willReturn('0');

        $sql = "SELECT COUNT(OXID) > 0
            FROM oxmanufacturers
            WHERE OXID = '42' AND  oxmanufacturers.oxactive = 1 ";

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')
            ->with($sql)
            ->willReturn($resultMock);

        $modelMock = $this->createMock(Manufacturer::class);
        $modelMock->method('getSqlActiveSnippet')
            ->with(true)
            ->willReturn('OXACTIVE = 1');
        $modelMock->method('getCoreTableName')
            ->willReturn('oxcategories_test');

        UtilsObject::setClassInstance(Manufacturer::class, $modelMock);

        $modifier = new ActiveModifier($databaseMock, Manufacturer::class, EshopRegistry::getUtilsObject());
        $type = new ManufacturerType(['id' => 42, 'active' => true]);
        $currentType = $modifier->apply($type);

        static::assertFalse($currentType->active);
    }
}

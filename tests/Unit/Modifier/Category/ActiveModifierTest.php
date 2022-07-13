<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Category;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Modifier\Category\ActiveModifier;
use Makaira\OxidConnectEssential\Type\Category\Category as CategoryType;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;

class ActiveModifierTest extends UnitTestCase
{
    public function testModifyTypeIfActive()
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchOne')
            ->willReturn('1');

        $sql = "SELECT COUNT(OXID) > 0
            FROM oxcategories_test
            WHERE OXID = '42' AND OXACTIVE = 1";

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')
            ->with($sql)
            ->willReturn($resultMock);

        $modelMock = $this->createMock(Category::class);
        $modelMock->method('getSqlActiveSnippet')
            ->with(true)
            ->willReturn('OXACTIVE = 1');
        $modelMock->method('getCoreTableName')
            ->willReturn('oxcategories_test');

        EshopRegistry::getUtilsObject()->setClassInstance(Category::class, $modelMock);

        $modifier = new ActiveModifier($databaseMock, Category::class, EshopRegistry::getUtilsObject());
        $type = new CategoryType(['id' => 42, 'active' => false]);
        $currentType = $modifier->apply($type);

        static::assertTrue($currentType->active);
    }

    public function testModifyTypeIfInctive()
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchOne')
            ->willReturn('0');

        $sql = "SELECT COUNT(OXID) > 0
            FROM oxcategories_test
            WHERE OXID = '42' AND OXACTIVE = 1";

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')
            ->with($sql)
            ->willReturn($resultMock);

        $modelMock = $this->createMock(Category::class);
        $modelMock->method('getSqlActiveSnippet')
            ->with(true)
            ->willReturn('OXACTIVE = 1');
        $modelMock->method('getCoreTableName')
            ->willReturn('oxcategories_test');

        EshopRegistry::getUtilsObject()->setClassInstance(Category::class, $modelMock);

        $modifier = new ActiveModifier($databaseMock, Category::class, EshopRegistry::getUtilsObject());
        $type = new CategoryType(['id' => 42, 'active' => true]);
        $currentType = $modifier->apply($type);

        static::assertFalse($currentType->active);
    }
}

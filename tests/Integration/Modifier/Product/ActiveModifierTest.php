<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Modifier\Product\ActiveModifier;
use Makaira\OxidConnectEssential\Type\Product\Product as ProductType;
use OxidEsales\Eshop\Application\Model\Category;
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
            FROM oxcategories
            WHERE OXID = '42' AND (  oxcategories.oxactive = 1  and  oxcategories.oxhidden = '0'  ) ";

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

        UtilsObject::setClassInstance(Category::class, $modelMock);

        $modifier = new ActiveModifier($databaseMock, Category::class, EshopRegistry::getUtilsObject());
        $type = new ProductType(['id' => 42, 'active' => false]);
        $currentType = $modifier->apply($type);

        static::assertTrue($currentType->active);
    }

    public function testModifyTypeIfInctive(): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchOne')
            ->willReturn('0');

        $sql = "SELECT COUNT(OXID) > 0
            FROM oxcategories
            WHERE OXID = '42' AND (  oxcategories.oxactive = 1  and  oxcategories.oxhidden = '0'  ) ";

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

        UtilsObject::setClassInstance(Category::class, $modelMock);

        $modifier = new ActiveModifier($databaseMock, Category::class, EshopRegistry::getUtilsObject());
        $type = new ProductType(['id' => 42, 'active' => true]);
        $currentType = $modifier->apply($type);

        static::assertFalse($currentType->active);
    }
}

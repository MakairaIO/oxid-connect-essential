<?php

namespace Makaira\OxidConnectEssential\Test\Unit\RevisionHandler\Extractor;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Category;
use OxidEsales\Eshop\Application\Model\Category as OxidCategory;
use OxidEsales\Eshop\Application\Model\Manufacturer as OxidManufacturer;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\TestingLibrary\UnitTestCase;

class CategoryTest extends UnitTestCase
{
    public function testItSupportsCategoryModel()
    {
        $dataExtractor = new Category(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $actual = $dataExtractor->supports(new OxidCategory());
        $this->assertTrue($actual);
    }

    public function testItDoesNotSupportManufacturerModel()
    {
        $dataExtractor = new Category(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $actual = $dataExtractor->supports(new OxidManufacturer());
        $this->assertFalse($actual);
    }

    public function testCreatesRevisionsForCategoryAndProducts()
    {
        $productIds = [
            'product1' => '',
            'product2' => '',
            'product3' => '',
            'variant1' => 'product1',
            'variant2' => 'product1',
            'variant3' => 'product1',
        ];

        $statementMock = $this->createMock(Statement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with(['phpunit42']);

        $statementMock
            ->expects($this->once())
            ->method('fetchAllKeyValue')
            ->willReturn($productIds);

        $sql = 'SELECT o2c.OXOBJECTID, a.OXPARENTID FROM `phpunit_oxobject2category_de` o2c ';
        $sql .= 'LEFT JOIN `phpunit_oxarticles_de` a ON a.`OXID` = o2c.`OXOBJECTID` WHERE o2c.`OXCATNID` = ?';
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statementMock);

        $viewNameCallback = function (string $table) {
            switch ($table) {
                case 'oxarticles':
                    return 'phpunit_oxarticles_de';
                case 'oxobject2category':
                    return 'phpunit_oxobject2category_de';
                default:
                    return 'phpunit_42_table';
            }
        };

        $viewNameGenerator = $this->createMock(TableViewNameGenerator::class);
        $viewNameGenerator->method('getViewName')->willReturnCallback($viewNameCallback);

        $model = $this->createMock(OxidCategory::class);
        $model->method('getId')->willReturn('phpunit42');

        $categoryExtractor = new Category($db, $viewNameGenerator);
        $actual            = $categoryExtractor->extract($model);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $expected = [
            Revision::TYPE_CATEGORY . '-phpunit42' => new Revision(Revision::TYPE_CATEGORY, 'phpunit42', $changed),
            Revision::TYPE_PRODUCT . '-product1'   => new Revision(Revision::TYPE_PRODUCT, 'product1', $changed),
            Revision::TYPE_PRODUCT . '-product2'   => new Revision(Revision::TYPE_PRODUCT, 'product2', $changed),
            Revision::TYPE_PRODUCT . '-product3'   => new Revision(Revision::TYPE_PRODUCT, 'product3', $changed),
            Revision::TYPE_VARIANT . '-variant1'   => new Revision(Revision::TYPE_VARIANT, 'variant1', $changed),
            Revision::TYPE_VARIANT . '-variant2'   => new Revision(Revision::TYPE_VARIANT, 'variant2', $changed),
            Revision::TYPE_VARIANT . '-variant3'   => new Revision(Revision::TYPE_VARIANT, 'variant3', $changed),
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }
}

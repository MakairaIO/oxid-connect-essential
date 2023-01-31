<?php

namespace Makaira\OxidConnectEssential\Test\Integration\RevisionHandler\Extractor;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\SelectList;
use OxidEsales\Eshop\Application\Model\Category as OxidCategory;
use OxidEsales\Eshop\Application\Model\Manufacturer as OxidManufacturer;
use OxidEsales\Eshop\Application\Model\SelectList as SelectListModel;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use PHPUnit\Framework\TestCase;

class SelectListTest extends TestCase
{
    public function testItSupportsSelectListModel(): void
    {
        $dataExtractor = new SelectList(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $model = new SelectListModel();
        $model->setId('phpunit_select_list');

        $actual = $dataExtractor->supports($model);
        $this->assertTrue($actual);
    }

    public function testItDoesNotSupportCategoryModel(): void
    {
        $dataExtractor = new SelectList(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $model = new OxidCategory();
        $model->setId('phpunit_category');

        $actual = $dataExtractor->supports($model);
        $this->assertFalse($actual);
    }

    public function testCreatesRevisionsForProducts(): void
    {
        $productIds = [
            'product1' => '',
            'product2' => '',
            'product3' => '',
            'variant1' => 'product1',
            'variant2' => 'product1',
            'variant3' => 'product1',
        ];

        $resultMock = $this->createMock(Result::class);
        $resultMock->expects($this->once())
            ->method('fetchAllKeyValue')
            ->willReturn($productIds);

        $statementMock = $this->createMock(Statement::class);
        $statementMock
            ->expects($this->once())
            ->method('executeQuery')
            ->with(['phpunit42'])
            ->willReturn($resultMock);


        $sql = 'SELECT `o2sl`.`OXOBJECTID`, `a`.`OXPARENTID` FROM `phpunit_oxobject2selectlist_de` `o2sl` ';
        $sql .= 'LEFT JOIN `phpunit_oxarticles_de` `a` ON `a`.`OXID` = `o2sl`.`OXOBJECTID` WHERE `o2sl`.`OXSELNID` = ?';

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statementMock);

        $viewNameCallback = function (string $table) {
            switch ($table) {
                case 'oxobject2selectlist':
                    return 'phpunit_oxobject2selectlist_de';
                case 'oxarticles':
                    return 'phpunit_oxarticles_de';
                default:
                    return 'phpunit_42_table';
            }
        };

        $viewNameGenerator = $this->createMock(TableViewNameGenerator::class);
        $viewNameGenerator->method('getViewName')->willReturnCallback($viewNameCallback);

        $model = $this->createMock(OxidManufacturer::class);
        $model->method('getId')->willReturn('phpunit42');

        $categoryExtractor = new SelectList($db, $viewNameGenerator);
        $actual            = $categoryExtractor->extract($model);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $productType      = Revision::TYPE_PRODUCT;
        $variantType      = Revision::TYPE_VARIANT;
        $expected         = [
            $productType . '-product1'       => new Revision($productType, 'product1', $changed),
            $productType . '-product2'       => new Revision($productType, 'product2', $changed),
            $productType . '-product3'       => new Revision($productType, 'product3', $changed),
            $variantType . '-variant1'       => new Revision($variantType, 'variant1', $changed),
            $variantType . '-variant2'       => new Revision($variantType, 'variant2', $changed),
            $variantType . '-variant3'       => new Revision($variantType, 'variant3', $changed),
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }
}

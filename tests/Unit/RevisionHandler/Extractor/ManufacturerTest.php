<?php

namespace Makaira\OxidConnectEssential\Test\Unit\RevisionHandler\Extractor;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Manufacturer;
use OxidEsales\Eshop\Application\Model\Article as OxidArticle;
use OxidEsales\Eshop\Application\Model\Manufacturer as OxidManufacturer;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\TestingLibrary\UnitTestCase;

class ManufacturerTest extends UnitTestCase
{
    public function testItSupportsManufacturerModel()
    {
        $dataExtractor = new Manufacturer(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $model = new OxidManufacturer();
        $model->setId('phpunit_manufacturer');

        $actual = $dataExtractor->supports($model);
        $this->assertTrue($actual);
    }

    public function testItDoesNotSupportProductModel()
    {
        $dataExtractor = new Manufacturer(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $model = new OxidArticle();
        $model->setId('phpunit_article');

        $actual = $dataExtractor->supports($model);
        $this->assertFalse($actual);
    }

    public function testCreatesRevisionsForManufacturerAndProducts()
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
        $resultMock
            ->expects($this->once())
            ->method('fetchAllKeyValue')
            ->willReturn($productIds);

        $statementMock = $this->createMock(Statement::class);
        $statementMock
            ->expects($this->once())
            ->method('executeQuery')
            ->with(['phpunit42'])
            ->willReturn($resultMock);

        $sql = "SELECT a.OXID, a.OXPARENTID FROM `phpunit_oxarticles_de` a WHERE a.`OXMANUFACTURERID` = ?";

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statementMock);

        $viewNameCallback = function (string $table) {
            return ($table === 'oxarticles') ? 'phpunit_oxarticles_de' : 'phpunit_42_table';
        };

        $viewNameGenerator = $this->createMock(TableViewNameGenerator::class);
        $viewNameGenerator->method('getViewName')->willReturnCallback($viewNameCallback);

        $model = $this->createMock(OxidManufacturer::class);
        $model->method('getId')->willReturn('phpunit42');

        $categoryExtractor = new Manufacturer($db, $viewNameGenerator);
        $actual            = $categoryExtractor->extract($model);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $manufacturerType = Revision::TYPE_MANUFACTURER;
        $productType      = Revision::TYPE_PRODUCT;
        $variantType      = Revision::TYPE_VARIANT;
        $expected         = [
            $manufacturerType . '-phpunit42' => new Revision($manufacturerType, 'phpunit42', $changed),
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

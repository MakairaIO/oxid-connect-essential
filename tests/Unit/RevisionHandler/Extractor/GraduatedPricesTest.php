<?php

namespace Makaira\OxidConnectEssential\Test\Unit\RevisionHandler\Extractor;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\GraduatedPrices;
use OxidEsales\Eshop\Application\Model\Manufacturer as OxidManufacturer;
use OxidEsales\Eshop\Application\Model\Object2Category;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\TestingLibrary\UnitTestCase;

class GraduatedPricesTest extends UnitTestCase
{
    public function testItSupportsBaseModel()
    {
        $dataExtractor = new GraduatedPrices($this->createMock(Connection::class));

        $model = new BaseModel();
        $model->init('oxprice2article');

        $actual = $dataExtractor->supports($model);
        $this->assertTrue($actual);
    }

    public function testItDoesNotSupportManufacturerModel()
    {
        $dataExtractor = new GraduatedPrices($this->createMock(Connection::class));

        $actual = $dataExtractor->supports(new OxidManufacturer());
        $this->assertFalse($actual);
    }

    /**
     * @param string $parentId
     * @param string $expectedType
     *
     * @return void
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @dataProvider provideTestData
     */
    public function testReturnsRevisionObject(string $parentId, string $expectedType)
    {
        $statementMock = $this->createMock(Statement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with(['phpunit42']);

        $statementMock
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($parentId);

        $sql = "SELECT `OXPARENTID` FROM `oxarticles` WHERE `OXID` = ?";

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statementMock);

        $model = $this->createMock(Object2Category::class);
        $model->method('getRawFieldData')->with('oxobjectid')->willReturn('phpunit42');

        $articleExtractor = new GraduatedPrices($db);
        $actual = $articleExtractor->extract($model);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $expected = [
            $expectedType . '-phpunit42' => new Revision($expectedType, 'phpunit42', $changed)
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function provideTestData()
    {
        return [
            'Testing product' => ['', Revision::TYPE_PRODUCT],
            'Testing variant' => ['phpunit21', Revision::TYPE_VARIANT]
        ];
    }
}

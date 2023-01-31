<?php

namespace Makaira\OxidConnectEssential\Test\Integration\RevisionHandler\Extractor;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\ArticleSelectList;
use OxidEsales\Eshop\Application\Model\Article as OxidArticle;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use PHPUnit\Framework\TestCase;

class ArticleSelectListTest extends TestCase
{
    public function testItSupportsBaseModel(): void
    {
        $dataExtractor = new ArticleSelectList(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $model = new BaseModel();
        $model->init('oxobject2selectlist');
        $model->assign(['oxobjectid' => 'phpunit_article']);

        $actual = $dataExtractor->supports($model);
        $this->assertTrue($actual);
    }

    public function testItDoesNotSupportProductModel(): void
    {
        $dataExtractor = new ArticleSelectList(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $model = new OxidArticle();
        $model->setId('phpunit_article');

        $actual = $dataExtractor->supports($model);
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
    public function testReturnsRevisionObject(string $parentId, string $expectedType): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($parentId);

        $statementMock = $this->createMock(Statement::class);
        $statementMock
            ->expects($this->once())
            ->method('executeQuery')
            ->with(['phpunit42'])
            ->willReturn($resultMock);

        $sql = "SELECT `OXPARENTID` FROM `phpunit_oxarticles_de` WHERE `OXID` = ?";

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statementMock);

        $viewNameGenerator = $this->createMock(TableViewNameGenerator::class);
        $viewNameGenerator
            ->expects($this->once())
            ->method('getViewName')
            ->with('oxarticles')
            ->willReturn('phpunit_oxarticles_de');

        $model = $this->createMock(BaseModel::class);
        $model->method('getRawFieldData')->with('oxobjectid')->willReturn('phpunit42');

        $articleExtractor = new ArticleSelectList($db, $viewNameGenerator);
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

    public function provideTestData(): array
    {
        return [
            'Testing product' => ['', Revision::TYPE_PRODUCT],
            'Testing variant' => ['phpunit21', Revision::TYPE_VARIANT]
        ];
    }
}

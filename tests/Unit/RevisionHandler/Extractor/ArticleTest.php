<?php

namespace Makaira\OxidConnectEssential\Test\Unit\RevisionHandler\Extractor;

use DateTimeImmutable;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Article;
use OxidEsales\Eshop\Application\Model\Article as OxidArticle;
use OxidEsales\Eshop\Application\Model\Category as OxidCategory;
use OxidEsales\TestingLibrary\UnitTestCase;

class ArticleTest extends UnitTestCase
{
    public function testItSupportsArticleModel()
    {
        $articleExtractor = new Article();
        $actual = $articleExtractor->supports(new OxidArticle());
        $this->assertTrue($actual);
    }

    public function testItDoesNotSupportCategoryModel()
    {
        $articleExtractor = new Article();
        $actual = $articleExtractor->supports(new OxidCategory());
        $this->assertFalse($actual);
    }

    /**
     * @return void
     * @dataProvider provideTestData
     */
    public function testReturnsRevisionObject(string $parentId, string $expectedType)
    {
        $article = $this->createMock(OxidArticle::class);
        $article->method('getParentId')->willReturn($parentId);
        $article->method('getId')->willReturn('phpunit42');

        $articleExtractor = new Article();
        $actual = $articleExtractor->extract($article);

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

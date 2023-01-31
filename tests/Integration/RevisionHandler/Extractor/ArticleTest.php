<?php

namespace Makaira\OxidConnectEssential\Test\Integration\RevisionHandler\Extractor;

use DateTimeImmutable;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Article;
use OxidEsales\Eshop\Application\Model\Article as OxidArticle;
use OxidEsales\Eshop\Application\Model\Category as OxidCategory;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testItSupportsArticleModel(): void
    {
        $dataExtractor = new Article();


        $model = new OxidArticle();
        $model->setId('phpunit_article');

        $actual = $dataExtractor->supports($model);
        $this->assertTrue($actual);
    }

    public function testItDoesNotSupportCategoryModel(): void
    {
        $dataExtractor = new Article();

        $model = new OxidCategory();
        $model->setId('phpunit_category');

        $actual = $dataExtractor->supports($model);
        $this->assertFalse($actual);
    }

    /**
     * @return void
     * @dataProvider provideTestData
     */
    public function testReturnsRevisionObject(string $parentId, string $expectedType): void
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

    public function provideTestData(): array
    {
        return [
            'Testing product' => ['', Revision::TYPE_PRODUCT],
            'Testing variant' => ['phpunit21', Revision::TYPE_VARIANT]
        ];
    }
}

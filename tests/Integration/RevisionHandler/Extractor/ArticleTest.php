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

    public function testReturnsRevisionForParentWithoutVariants(): void
    {
        $article = $this->createMock(OxidArticle::class);
        $article
            ->method('getParentId')->willReturn('')
            ->method('getId')->willReturn('phpunit-parent')
            ->method('getVariants')->willReturn([]);

        $articleExtractor = new Article();
        $actual           = $articleExtractor->extract($article);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $expected = [
            Revision::TYPE_PRODUCT . '-phpunit-parent' => new Revision(
                Revision::TYPE_PRODUCT,
                'phpunit-parent',
                $changed,
            ),
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }


    public function testReturnsRevisionForParentWithVariants(): void
    {
        $article = $this->createMock(OxidArticle::class);
        $article
            ->method('getParentId')->willReturn('')
            ->method('getId')->willReturn('phpunit-parent')
            ->method('getVariantIds')->willReturn(['phpunit-variant1', 'phpunit-variant2']);

        $articleExtractor = new Article();
        $actual           = $articleExtractor->extract($article);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $expected = [
            Revision::TYPE_PRODUCT . '-phpunit-parent'   => new Revision(
                Revision::TYPE_PRODUCT,
                'phpunit-parent',
                $changed,
            ),
            Revision::TYPE_VARIANT . '-phpunit-variant1' => new Revision(
                Revision::TYPE_VARIANT,
                'phpunit-variant1',
                $changed,
            ),
            Revision::TYPE_VARIANT . '-phpunit-variant2' => new Revision(
                Revision::TYPE_VARIANT,
                'phpunit-variant2',
                $changed,
            ),
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testReturnsRevisionObjectsForVariant(): void
    {
        $article = $this->createMock(OxidArticle::class);
        $article
            ->method('getParentId')->willReturn('phpunit-parent')
            ->method('getId')->willReturn('phpunit-variant1');

        $articleExtractor = new Article();
        $actual           = $articleExtractor->extract($article);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $expected = [
            Revision::TYPE_PRODUCT . '-phpunit-parent'   => new Revision(
                Revision::TYPE_PRODUCT,
                'phpunit-parent',
                $changed,
            ),
            Revision::TYPE_VARIANT . '-phpunit-variant1' => new Revision(
                Revision::TYPE_VARIANT,
                'phpunit-variant1',
                $changed,
            ),
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }
}

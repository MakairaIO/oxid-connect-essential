<?php

namespace Makaira\OxidConnectEssential\Test\Integration\RevisionHandler;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Article;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Category;
use Makaira\OxidConnectEssential\RevisionHandler\ModelDataExtractor;
use Makaira\OxidConnectEssential\RevisionHandler\ModelNotSupportedException;
use OxidEsales\Eshop\Application\Model\Article as OxidArticle;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use PHPUnit\Framework\TestCase;

class ModelDataExtractorTest extends TestCase
{
    /**
     * @throws ModelNotSupportedException
     * @dataProvider provideProducts
     */
    public function testCanExtractDataFromProduct(string $productId, string $parentId): void
    {
        $categoryExtractor = new Category(
            $this->createMock(Connection::class),
            $this->createMock(TableViewNameGenerator::class)
        );

        $productExtractor = new Article();

        $extractors = [$categoryExtractor, $productExtractor];

        $extractor = new ModelDataExtractor($extractors);

        $product = new OxidArticle();
        $product->assign(['oxid' => $productId, 'oxparentid' => $parentId]);
        $actual = $extractor->extractData($product);

        $changed = new DateTimeImmutable();

        foreach ($actual as $revision) {
            $revision->changed = $changed;
        }

        $expected = [
            Revision::TYPE_PRODUCT . '-phpunit42' => new Revision(Revision::TYPE_PRODUCT, 'phpunit42', $changed),
        ];

        if ($parentId) {
            $expected = [
                Revision::TYPE_PRODUCT . '-phpunit21' => new Revision(Revision::TYPE_PRODUCT, 'phpunit21', $changed),
                Revision::TYPE_VARIANT . '-phpunit42' => new Revision(Revision::TYPE_VARIANT, 'phpunit42', $changed),
            ];
        }


        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function provideProducts(): array
    {
        return [
            ['phpunit42', ''],
            ['phpunit42', 'phpunit21'],
        ];
    }
}

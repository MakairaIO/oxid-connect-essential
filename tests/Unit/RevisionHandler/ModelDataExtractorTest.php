<?php

namespace Makaira\OxidConnectEssential\Test\Unit\RevisionHandler;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Article;
use Makaira\OxidConnectEssential\RevisionHandler\Extractor\Category;
use Makaira\OxidConnectEssential\RevisionHandler\ModelDataExtractor;
use OxidEsales\Eshop\Application\Model\Article as OxidArticle;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\TestingLibrary\UnitTestCase;

class ModelDataExtractorTest extends UnitTestCase
{
    /**
     * @param string $productId
     * @param string $parentId
     * @param string $expectedType
     *
     * @return void
     * @throws \Makaira\OxidConnectEssential\RevisionHandler\ModelNotSupportedException
     * @dataProvider provideProducts
     */
    public function testCanExtractDataFromProduct(string $productId, string $parentId, string $expectedType)
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
            $expectedType . '-phpunit42' => new Revision($expectedType, 'phpunit42', $changed),
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function provideProducts()
    {
        return [
            ['phpunit42', '', Revision::TYPE_PRODUCT],
            ['phpunit42', 'phpunit21', Revision::TYPE_VARIANT],
        ];
    }
}

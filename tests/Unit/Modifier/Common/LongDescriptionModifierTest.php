<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier\Common\LongDescriptionModifier;
use Makaira\OxidConnectEssential\Type\Product\Product;
use Makaira\OxidConnectEssential\Utils\ContentParserInterface;
use PHPUnit\Framework\TestCase;

class LongDescriptionModifierTest extends TestCase
{
    /**
     * @param LongDescriptionModifier $modifier
     * @param Product                 $product
     *
     * @return void
     * @dataProvider provideTestData
     */
    public function testRunner(LongDescriptionModifier $modifier, Product $product): void
    {
        $product = $modifier->apply($product);
        $this->assertEquals('This is a short text', $product->longdesc);
    }

    public function provideTestData(): array
    {
        $parserMock = $this->createMock(ContentParserInterface::class);
        $parserMock
            ->method('parseContent')
            ->willReturnArgument(1);

        $modifier = new LongDescriptionModifier($parserMock, true);

        return [
            'Short Text' => [
                $modifier,
                new Product(['id' => 'phpunit_1', 'longdesc' => 'This is a short text']),
            ],
            'Short Text with HTML' => [
                $modifier,
                new Product(['id' => 'phpunit_2', 'longdesc' => 'This is a <del>short</del> text']),
            ],
            'Trimmed Short Text' => [
                $modifier,
                new Product(['id' => 'phpunit_3', 'longdesc' => '   This is a short text   ']),
            ],
        ];
    }
}
